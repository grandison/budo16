<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: LogRotation.php 8312 2011-01-26 00:38:51Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Task_LogRotation extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    // Get configuration
    $fileLimit = Engine_Api::_()->getApi('settings', 'core')
        ->getSetting('core.log.filelimit', 5);
    $sizeLimit = Engine_Api::_()->getApi('settings', 'core')
        ->getSetting('core.log.sizelimit', 10 * 1024 * 1024);

    // Get a list of files in temporary/log
    $logPath = APPLICATION_PATH . DIRECTORY_SEPARATOR
        . 'temporary' . DIRECTORY_SEPARATOR
        . 'log';
    $it = new DirectoryIterator($logPath);

    // Check for log files
    $logFiles = array();
    $logFileParts = array();
    foreach( $it as $file ) {
      if( !$file->isFile() ) {
        continue;
      }
      $filename = $file->getFilename();
      $p1 = strrpos($filename, '.');
      if( !$p1 ) {
        continue;
      }
      $p2 = strrpos($filename, '.', $p1 - strlen($filename) - 1);
      if( $p2 ) {
        $logName = trim(substr($filename, 0, $p2), '.');
        $logNumber = trim(substr($filename, $p1), '.');
        $logExtension = strtolower(trim(substr($filename, $p2, $p2 - $p1), '.'));
        if( $logExtension != 'log' || !is_numeric($logNumber) ) {
          continue;
        }
        $logFileParts[$logName][$logNumber] = true;
      } else {
        $logName = trim(substr($filename, 0, $p1), '.');
        $logNumber = null;
        $logExtension = strtolower(trim(substr($filename, $p1), '.'));
        if( $logExtension != 'log' ) {
          continue;
        }
        $logFiles[$logName] = filesize($file->getPathname());
      }
    }

    // Check if we should perform rotation
    foreach( $logFiles as $logName => $size ) {
      if( $size > $sizeLimit ) {
        // Perform rotation
        for( $i = $fileLimit; $i >= 0; $i-- ) {
          if( $i == $fileLimit && isset($logFileParts[$logName][$i]) ) {
            $file = $logPath . DIRECTORY_SEPARATOR . $logName . '.log.' . $i;
            if( is_file($file) ) {
              // Remove last file
              @unlink($file);
            }
          } else if( $i == 0 ) {
            $file = $logPath . DIRECTORY_SEPARATOR . $logName . '.log';
            $file2 = $logPath . DIRECTORY_SEPARATOR . $logName . '.log.' . ($i + 1);
            if( is_file($file) ) {
              // Rename original file
              @rename($file, $file2);
              // Touch+chmod original file?
              @touch($file);
              @chmod($file, 0666);
            }
          } else {
            $file = $logPath . DIRECTORY_SEPARATOR . $logName . '.log.' . $i;
            $file2 = $logPath . DIRECTORY_SEPARATOR . $logName . '.log.' . ($i + 1);
            // Rename other files
            if( is_file($file) ) {
              @rename($file, $file2);
            }
          }
        }
      }
    }
  }
}
