<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Statistics.php 7420 2010-09-20 02:55:35Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Task_Statistics extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    if( !Engine_Api::_()->getApi('settings', 'core')->core_license_statistics ) {
      $this->_setWasIdle();
      return;
    }
    
    // Get base info
    $url  = 'http://service.socialengine.net/?action=statistics';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $key  = Engine_Api::_()->getApi('settings', 'core')->core_license_key;

    $db = Engine_Db_Table::getDefaultAdapter();

    // Get basic data
    $data = array(
      // Get host data
      'host'        => $_SERVER['HTTP_HOST'],
      'path'        => dirname($_SERVER['SCRIPT_NAME']),
      'key'         => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.license.key'),

      // Get system data
      'os'          => php_uname(),
      'httpd'       => $_SERVER['SERVER_SOFTWARE'],
      'httpd_sig'   => trim(strip_tags($_SERVER['SERVER_SIGNATURE'])),

      // Get db data
      'db_adapter'  => get_class($db),
      'db_server'   => $db->getServerVersion(),

      // Get php data
      'php_sapi'    => php_sapi_name(),
      'php_version' => phpversion(),
      'php_zend'    => zend_version(),
    );

    // Get php extension info
    $extensionData = array();
    $extensions = get_loaded_extensions();
    if( version_compare(PHP_VERSION, '5.2.4', '>=') ) {
      $extensions = array_merge($extensions, get_loaded_extensions(true));
    }
    foreach( $extensions as $extension ) {
      $version = phpversion($extension);
      $extensionData[] = array(
        'extension' => $extension,
        'version' => $version,
      );
    }
    $data['php_extensions'] = $extensionData;

    // Get packages info
    $packagesData = array();
    foreach( scandir(APPLICATION_PATH . '/application/packages') as $file ) {
      if( strtolower(substr($file, -5)) != '.json' ) continue;
      $packagesData[] = substr($file, 0, -5);
    }
    $data['packages'] = $packagesData;
    
    // Get php info (and remove sensitive data)
    /*
    $phpinfo = $this->_phpinfo();
    unset($phpinfo['PHP Configuration']['Configuration File (php.ini) Path']);
    unset($phpinfo['PHP Configuration']['Loaded Configuration File']);
    unset($phpinfo['PHP Configuration']['Scan this dir for additional .ini files']);
    unset($phpinfo['PHP Configuration']['additional .ini files parsed']);
    unset($phpinfo['PHP Core']['include_path']);
    unset($phpinfo['HTTP Headers Information']);
    unset($phpinfo['Apache Environment']['PATH']);
    unset($phpinfo['apache2handler']['Server Root']);
    unset($phpinfo['interbase']['ibase.default_password']);
    unset($phpinfo['interbase']['ibase.default_user']);
    unset($phpinfo['mysql']['mysql.default_password']);
    unset($phpinfo['mysql']['mysql.default_user']);
    unset($phpinfo['mysqli']['mysqli.default_pw']);
    unset($phpinfo['mysqli']['mysqli.default_user']);
    unset($phpinfo['odbc']['odbc.default_pw']);
    unset($phpinfo['odbc']['odbc.default_user']);
    unset($phpinfo['Environment']);
    unset($phpinfo['PHP Variables']);
    $data['php_info'] = $phpinfo;
     * 
     */

    // Json encode
    $data = base64_encode(Zend_Json::encode($data));
    
    // Send
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'd=' . $data);
    $ret = curl_exec($ch);
    
    $ret = Zend_Json::decode($ret);

    if( !is_array($ret) || @$ret['responseStatus'] !== 200 ) {
      throw new Exception('unable to send stats');
    }
  }

  protected function _phpinfo()
  {
    ob_start();
    phpinfo(-1);
    $phpinfo = ob_get_clean();

    $phpinfo = preg_replace(array(
      '#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
      '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
      "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
      '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
      .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
      '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
      '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
      "# +#", '#<tr>#', '#</tr>#'
    ), array(
      '$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
      '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
      "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
      '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
      '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
      '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
    ), $phpinfo);

    $sections = explode('<h2>', strip_tags($phpinfo, '<h2><th><td>'));
    unset($sections[0]);

    $phpinfo = array();
    foreach( $sections as $section ) {
      $n = substr($section, 0, strpos($section, '</h2>'));
      preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $matches, PREG_SET_ORDER);
      foreach( $matches as $m ) {
        if( isset($m[2]) && (!isset($m[3]) || $m[2] == $m[3]) ) {
          $phpinfo[$n][$m[1]] = $m[2];
        } else {
          $phpinfo[$n][$m[1]] = array_slice($m,2);
        }
        //$phpinfo[$n][$m[1]] = ( !isset($m[3] ) || $m[2] == $m[3] ) ? $m[2] : array_slice($m,2);
      }
    }

    return $phpinfo;
  }
}