<?php

// Only support cli
if( 'cli' !== PHP_SAPI ) {
  exit(1);
}

// Initialize
define('_ENGINE_R_REWRITE', false);
define('_ENGINE_R_CONF', true);
define('_ENGINE_R_MAINTENANCE', false);

if( function_exists('ini_set') ) {
  ini_set('memory_limit', '64M');
}

// Preprocess request
$params = array();
$request = array();
for( $i = 1; $i < $argc; $i++ ) {
  if( $argv[$i][0] == '-' ) {
    if( strpos($argv[$i], '=') !== false ) {
      $opt = strtolower(substr($argv[$i], 1));
      list($opt, $optValue) = explode('=', $opt);
    } else {
      $opt = strtolower(substr($argv[$i], 1));
      $optValue = $argv[$i++];
    }
    switch( $opt ) {
      case 'method':
        $params['method'] = $optValue;
        break;
    }
  } else {
    if( strpos($argv[$i], ',') !== false ) {
      $arg = explode(',', $argv[$i]);
    } else {
      $arg = array($argv[$i]);
    }
    foreach( $arg as $oneArg ) {
      list($key, $value) = explode('=', $oneArg, 2);
      $request[$key] = $value;
    }
  }
}

// Setup
include dirname(__FILE__) . '/index.php';

$application->bootstrap();

// Setup request
$application->bootstrap('frontcontroller');
$front   = $application->getBootstrap()->getContainer()->frontcontroller;
//$front   = new Zend_Controller_Front();
$router = $front->getRouter();
$router->addDefaultRoutes();

//$action     = @$request['action'];
//$controller = @$request['controller'];
//$module     = @$request['module'];
//$reqParams  = array_diff_key($request, array('action' => null, 'controller' => null, 'module' => null));
//$request    = new Zend_Controller_Request_Simple($action, $controller, $module, $reqParams);

$uri = 'http://127.0.0.1' . $router->assemble($request, 'default', true);
$request = new Zend_Controller_Request_Http($uri);
$front->setRequest($request);

$response = new Zend_Controller_Response_Cli();
$front->setResponse($response);

// Run
$default = $front->getDefaultModule();
if (null === $front->getControllerDirectory($default)) {
    throw new Zend_Application_Bootstrap_Exception(
        'No default controller directory registered with front controller'
    );
}

$front->setParam('bootstrap', $application->getBootstrap());
$front->dispatch();

Zend_Session::writeClose();

echo PHP_EOL;
$response->sendResponse();
echo PHP_EOL;
