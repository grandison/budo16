<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Admin.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Controller_Action_Admin extends Core_Controller_Action_User
{
  public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
  {
    // Neuter
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
      $_SERVER['REQUEST_METHOD'] = 'GET';
      $_POST = array();
      $_FILES = array();
    }

    // Parent
    /*
    parent::__construct($request, $response, $invokeArgs);
     */
    $this->setRequest($request)
         ->setResponse($response)
         ->_setInvokeArgs($invokeArgs);
    $this->_helper = new Zend_Controller_Action_HelperBroker($this);
    $this->init();

    // Normal
    $this->_helper->contextSwitch->setLayout('smoothbox', 'admin-simple');
    if( !$this->_helper->requireAdmin->checkRequire() ) {
      if( !$this->_helper->requireUser()->isValid() ) {
        return;
      }
      $this->_helper->requireAdmin();
      return;
    }
    //$this->_helper->requireAdmin();

    // Reauthentication
    if( Engine_Api::_()->getApi('settings', 'core')->core_admin_reauthenticate ) {
      $session = new Zend_Session_Namespace('Core_Auth_Reauthenticate');
      $timeout = Engine_Api::_()->getApi('settings', 'core')->core_admin_timeout;
      if( $timeout && (time() > $timeout + $session->start) ) {
        unset($session->identity);
      }
      if( empty($session->identity) ) {
        return $this->_helper->redirector->gotoRoute(array('controller' => 'auth', 'action' => 'login'), 'admin_default', true);
      }
    }



    // Neuter
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
      $this->view->headScript()->appendScript("
window.addEvent('load', function() {
  $$('form[method=post] button[type=submit]')
    /*.set('disabled', true)*/
    .setStyles({
      'background-color' : '#868686',
      'border' : '1px solid #777777',
    })
    .addEvent('click', function(event) {
      event.stop();
      alert('disabled');
    });
});
");
    }
  }
  
  public function postDispatch()
  {
    $layoutHelper = $this->_helper->layout;
    if( $layoutHelper->isEnabled() && !$layoutHelper->getLayout() )
    {
      $layoutHelper->setLayout('admin');
    }
  }
}