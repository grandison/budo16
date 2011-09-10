<?php
/**
 * @package     Engine_Core
 * @version     $Id: mobile.php 7539 2010-10-04 04:41:38Z john $
 * @copyright   Copyright (c) 2008 Webligo Developments
 * @license     http://www.socialengine.net/license/
 */

$_GET['m'] = 'mobile'; // Trick main loader
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'index.php';

$application->bootstrap();

// Return site info
if( empty($_REQUEST['action']) ) {
  $site = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site');
  $info = array(
    'site' => $site
  );
  echo Zend_Json::encode($info);
  exit();
}

// Parse action param
$action = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $_GET['action']);
$action = explode('/', $action);
if( count($action) != 2 && count($action) != 3 ) {
  echo Zend_Json::encode(array('error' => true, 'core' => 404));
  exit();
}
$action = join('/', $action);

// Dispatch
$front = Zend_Controller_Front::getInstance();
//$front->setBaseUrl($front->getBaseUrl().'/index.php');
$request = new Zend_Controller_Request_Http('http://'.$_SERVER['HTTP_HOST'].$front->getBaseUrl().'/'.$action);
$request->setBaseUrl($front->getBaseUrl());
$front->dispatch($request);
