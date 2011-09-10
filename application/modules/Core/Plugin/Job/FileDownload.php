<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FileDownload.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Job_FileDownload extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    $file = $this->getParam('file');
    $url = $this->getParam('url');
    
    // File and url must be strings
    if( !is_string($file) || '' == $file ) {
      throw new Core_Model_Exception('File was not specified');
    }
    if( !is_string($url) || '' == $url ) {
      throw new Core_Model_Exception('URL was not specified');
    }

    // Parse url and check for validity
    $urlParts = parse_url($url);
    if( empty($urlParts['scheme']) ) {
      $urlParts['scheme'] = 'http';
    }
    if( empty($urlParts['host']) ) {
      throw new Core_Model_Exception('URL does not have a host');
    }
    if( empty($urlParts['path']) ) {
      $urlParts['path'] = '';
      //throw new Core_Model_Exception('URL does not have a path');
    }
    
    // Add application path if path isn't absolute
    if( $file[0] != '/' && $file[0] != '\\' && ($file[1] != ':' || $file[2] != '\\') ) {
      $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . $file;
    }
    
    // Check if file exists and is not writable
    if( file_exists($file) && !is_writable($file) ) {
      throw new Core_Model_Exception('File exists and is not writable');
    }

    // Check if parent directory doesn't exist or is not writable
    $parentDir = dirname($file);
    if( !is_dir($parentDir) || !is_writable($parentDir) ) {
      throw new Core_Model_Exception('Parent directory does not exist or is not writable');
    }


    // Prepare curl
    $ch = curl_init();
    $mh = curl_multi_init();
    $fh = fopen($file, 'w');

    // Initialize default options
    if( strtoupper($this->getParam('method', 'GET')) == 'POST' ) {
      curl_setopt($ch, CURLOPT_POST, true);
    }
    $versionInfo = curl_version();
    curl_setopt($ch, CURLOPT_USERAGENT, 'curl/' . $versionInfo['version'] . ' (' . PHP_OS . '; SocialEngine)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 3600); // Sigh @todo fix this
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_REFERER, '');

    // Initialize custom options
    if( null != ($params = $this->getParam('params')) && is_array($params) ) {
      foreach( $params as $key => $value ) {
        if( !is_string($key) ) continue;
        $key = 'CURLOPT_' . strtoupper($key);
        if( defined($key) ) {
          curl_setopt($ch, constant($key), $value);
        }
      }
    }

    // Initialize required options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fh);
    
    // Initialize mutli handle
    curl_multi_add_handle($mh, $ch);

    // Execute handles
    $running = null;
    $lastUpdate = 0;
    do {
      usleep(100 * 1000); // 100 milliseconds = 0.1 seconds
      curl_multi_exec($mh, $running);
      $info = curl_getinfo($ch);
      // Update info (once every second)
      if( microtime(true) > $lastUpdate + 1 ) {
        $lastUpdate = microtime(true);
        $this->_data['total'] = $info['download_content_length'];
        $this->_data['progress'] = $info['size_download'];
        $this->_data['speed'] = $info['speed_download'];
        $this->_data['mime'] = $info['mime'];
        // Update in db
        $this->_job->getTable()->update(array(
          'progress' => ($this->_data['total'] <= 0 ? 0 : $this->_data['progress'] / $this->_data['total']),
          'data' => Zend_Json::encode($this->_data),
        ), array(
          'job_id = ?' => $this->_job->job_id,
        ));
      }
    } while( $running > 0 );

    // Process info
    if( curl_errno($ch) > 0 ) {
      // throw on curl errors
      throw new Core_Model_Exception(curl_error($ch), curl_errno($ch));
    }

    // Close the handles
    curl_multi_remove_handle($mh, $ch);
    curl_multi_close($mh);
    curl_close($ch); // necessary?
    
    // Ok, we're done!
    $this->_setIsComplete(true);
  }
}