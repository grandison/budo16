<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: lite.php 8404 2011-02-05 03:32:38Z john $
 * @author     John
 */
$application->getBootstrap()->bootstrap('censor');

include dirname(__FILE__).'/controllers/AjaxController.php';
$controller = new Chat_AjaxController(new Zend_Controller_Request_Http(), new Zend_Controller_Response_Http());
$action = str_replace(' ', '', ucwords(str_replace(array('-', '.'), ' ', preg_replace('/[^a-z0-9.-]/', '', @$_REQUEST['action']))));
$controller->view = new stdClass();

$method = $action . 'Action';
if( method_exists($controller, $method) ) {
  header('Cache-control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0', true);
  header('Pragma: no-cache', true);
  $controller->$method();
  echo Zend_Json::encode($controller->view);
  exit();
} else {
  header("HTTP/1.0 404 Not Found");
  echo 'Action not found';
  echo "\n<!-- IE and Chrome will show their own personal 404 page when the page is shorter than 512 bytes in size.".
       str_repeat("\n     ".str_repeat('*', 100), 5).
       "\n     That should do it.\n-->";
  exit();
}
