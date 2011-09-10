<?php
/**
 * @package     Engine_Core
 * @version     $Id: lite.php 8395 2011-02-04 01:08:41Z john $
 * @copyright   Copyright (c) 2008 Webligo Developments
 * @license     http://www.socialengine.net/license/
 */

// Config
if( !defined('_ENGINE_R_MAIN') ) {
  define('_ENGINE_R_CONF', true);
  define('_ENGINE_R_INIT', true);
  include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'index.php';
}

// Create application, bootstrap, and run
$application = new Engine_Application(
  APPLICATION_ENV,
  array(
    'bootstrap' => array(
      'path' => APPLICATION_PATH . '/application/modules/' . APPLICATION_NAME . '/Bootstrap.php',
      'class' => ucfirst(APPLICATION_NAME) . '_Bootstrap',
    ),
    'autoloadernamespaces' => array(
      'Engine_',
    )
  )
);
Engine_Api::getInstance()->setApplication($application);
$application->getBootstrap()->bootstrap('frontcontroller');
$application->getBootstrap()->bootstrap('cache');
$application->getBootstrap()->bootstrap('db');
$application->getBootstrap()->bootstrap('frontcontrollermodules');
$application->getBootstrap()->bootstrap('session');
$application->getBootstrap()->bootstrap('manifest');
$application->getBootstrap()->bootstrap('router');
$application->getBootstrap()->bootstrap('view');
$application->getBootstrap()->bootstrap('modules');

$module = str_replace(' ', '', ucwords(str_replace(array('-', '.'), ' ', preg_replace('/[^a-z0-9.-]/', '', @$_REQUEST['module']))));
$name = preg_replace('/[^a-z0-9]/', '', @$_REQUEST['name']);
if( !$name ) $name = 'lite';
$file = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $name . '.php';
if( !file_exists($file) ) {
  header("HTTP/1.0 404 Not Found");
  echo 'not found';
  if( APPLICATION_ENV === 'development' ) {
    echo $module;
    echo '<br />';
    echo $name;
    echo '<br />';
    echo $file;
  }
  echo "\n<!-- IE and Chrome will show their own personal 404 page when the page is shorter than 512 bytes in size.".
       str_repeat("\n     ".str_repeat('*', 100), 5).
       "\n     That should do it.\n-->";
  exit();
}

include_once $file;
