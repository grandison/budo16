<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminLogController.php 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminLogController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
//    $logAdapter = Engine_Api::_()->getDbtable('settings', 'core')
//      ->getSetting('core.log.adapter', 'file');

    $logPath = APPLICATION_PATH . '/temporary/log';

    // Get all existing log files
    $logFiles = array();
    foreach( scandir($logPath) as $file ) {
      if( strtolower(substr($file, -4)) == '.log' ) {
        $logFiles[] = substr($file, 0, -4);
      }
    }

    // No files
    $this->view->logFiles = $logFiles;
    if( empty($logFiles) ) {
      $this->view->error = 'There are no log files to view.';
      return;
    }

    // Make form
    $labels = array(
      'main' => 'Error log',
      'tasks' => 'Task scheduler log',
      'translate' => 'Language log',
      'video' => 'Video encoding log',
    );
    $multiOptions = array_combine($logFiles, $logFiles);
    $labels = array_intersect_key($labels, $multiOptions);
    $multiOptions = array_diff_key($multiOptions, $labels);
    $multiOptions = array_merge($labels, $multiOptions);

    $this->view->formFilter = $formFilter = new Core_Form_Admin_System_Log();
    $formFilter->getElement('file')->addMultiOptions($multiOptions);
    //$formFilter->getElement('file')->setValue(key($logFiles));

    $values = array_merge(array(
      //'file' => current($logFiles),
      'length' => 50,
    ), $this->_getAllParams());

    if( $formFilter->isValid($values) ) {
      $values = $formFilter->getValues();
    } else {
      $values = array('length' => 50);
    }

    /*
    if( empty($values['file']) ) {
      $values = array(
        'file' => current($logFiles),
        'length' => 50,
      );
    }
     */

    // Make sure param is in existing log files
    $logName = @$values['file'];
    $logFile = $logName . '.log';
    if( empty($logName) ||
        !in_array($logName, $logFiles) ||
        !file_exists($logPath . DIRECTORY_SEPARATOR . $logFile) ) {
      $logName = null;
      $logFile = null;
    }

    // Exit if no valid log file
    if( !$logFile ) {
      $this->view->error = 'Please select a file to view.';
      return;
    }

    // Clear log if requested
    if( $this->getRequest()->isPost() && $this->_getParam('clear', false) ) {
      if( ($fh = fopen($logPath . DIRECTORY_SEPARATOR . $logFile, 'w')) ){
        ftruncate($fh, 0);
        fclose($fh);
      }
      return $this->_helper->redirector->gotoRoute(array());
    }

    // Get log length
    $this->view->logFile = $logFile;
    $this->view->logSize = $logSize = filesize($logPath . DIRECTORY_SEPARATOR . $logFile);
    $this->view->logLength = $logLength = $values['length'];
    $this->view->logOffset = $logOffset = $this->_getParam('offset', $logSize);


    // Tail the file
    $endOffset = 0;
    try {
      $lines = $this->_tail($logPath . DIRECTORY_SEPARATOR . $logFile, $logLength, $logOffset, true, $endOffset);
    } catch( Exception $e ) {
      $this->view->error = $e->getMessage();
      return;
    }

    $this->view->logText = $lines;
    $this->view->logEndOffset = $endOffset;
  }

  public function downloadAction()
  {
    $logPath = APPLICATION_PATH . '/temporary/log';

    // Get all existing log files
    $logFiles = array();
    foreach( scandir($logPath) as $file ) {
      if( strtolower(substr($file, -4)) == '.log' ) {
        $logFiles[] = $file;
      }
    }

    $logFile = $this->_getParam('file');
    if( empty($logFile) ||
        !in_array($logFile, $logFiles) ||
        !file_exists($logPath . DIRECTORY_SEPARATOR . $logFile) ) {
      exit();
    }

    // kill output buffering
    while( ob_get_level() > 0 ) {
      ob_end_clean();
    }

    // Send headers
    header('content-disposition: attachment, filename=' . urlencode($logFile));
    header('content-length: ' . filesize($logPath . DIRECTORY_SEPARATOR . $logFile));

    // Open file
    $handle = fopen($logPath . DIRECTORY_SEPARATOR . $logFile, 'r');
    while( '' !== ($str = fread($handle, 1024)) ) {
      echo $str;
    }
    exit();
  }

  public function settingsAction()
  {
    
  }

  protected function _tail($file, $length = 10, $offset = 0, $whence = true, &$endOffset = null)
  {
    // Check stuff
    if( !file_exists($file) ) {
      throw new Exception('File does not exist.');
    }
    if( 0 === ($size = filesize($file)) ) {
      throw new Exception('File is empty.');
    }
    if( !($fh = fopen($file, 'r')) ) {
      throw new Exception('Unable to open file.');
    }

    // Process args
    if( abs($offset) > $size ) {
      throw new Exception('Reached end of file.');
    }
    if( !in_array($whence, array(SEEK_SET, SEEK_END)) ) {
      throw new Exception('Unknown whence.');
    }

    // Seek to requested position
    fseek($fh, $offset, SEEK_SET);

    // Read in chunks of 512 bytes
    $position = $offset;
    $break = false;
    $lines = array();
    $chunkSize = 512;
    $buffer = '';

    do {

      // Get next position
      $position += ( $whence ? -$chunkSize : $chunkSize );
      fseek($fh, $position, SEEK_SET);

      // Whoops we ran out of stuff
      if( $position < 0 || $position > $size ) {
        $break = true;
        break;
      }

      // Read a chunk
      $chunk = fread($fh, $chunkSize);
      if( $whence ) {
        $buffer = $chunk . $buffer;
      } else {
        $buffer .= $chunk;
      }

      // Parse chunk into lines
      $bufferLines = preg_split('/\r\n?|\n/', $buffer);

      // Put the last (probably incomplete) one back
      if( $whence ) {
        $buffer = array_shift($bufferLines);
      } else {
        $buffer = array_pop($bufferLines);
      }

      // Add to existing lines
      if( $whence ) {
        $lines = array_merge($bufferLines, $lines);
      } else {
        $lines = array_merge($lines, $bufferLines);
      }

      // Are we done?
      if( count($lines) >= $length ) {
        $break = true;
      }

    } while( !$break );

    $endOffset = $position;

    // Add remaining length in buffer
    $endOffset += strlen($buffer);

    return trim(join(PHP_EOL, $lines), "\n\r");
  }
}