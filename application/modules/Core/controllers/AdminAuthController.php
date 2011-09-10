<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminAuthController.php 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminAuthController extends Core_Controller_Action_Standard
{
  public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
  {
    parent::__construct($request, $response, $invokeArgs);
  }

  public function postDispatch()
  {
    $layoutHelper = $this->_helper->layout;
    if( $layoutHelper->isEnabled() && !$layoutHelper->getLayout() )
    {
      $layoutHelper->setLayout('admin-simple');
    }
  }

  public function loginAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Core_Form_Admin_Auth_Login();

    if( !$this->getRequest()->isPost() ) {
      $form->populate(array(
        'return' => $this->_getParam('return', @$_SERVER['HTTP_REFERER']),
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = Engine_Api::_()->getApi('settings', 'core')->core_admin;
    $staticSalt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');

    $password = $form->getValue('password');

    switch( @$values['mode'] ) {
      case 'none':
        $form->addError('No reauthentication.');
        return;
        break;
      case 'user':
        // Psh, redirect to user end
        if( !$viewer || !$viewer->getIdentity() ) {
          return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }
        if( $viewer->password != md5($staticSalt . $password . $viewer->salt) ) {
          $form->addError('Invalid login');
          return;
        } else {
          $valid = true;
        }
        break;
      case 'global':
        if( empty($values['password']) || $values['password'] != md5($staticSalt . $password) ) {
          $form->addError('Invalid login');
          return;
        } else {
          $valid = true;
        }
        break;
      default:
        $form->addError('Unknown method.');
        return;
        break;
    }

    if( $valid ) {
      $session = new Zend_Session_Namespace('Core_Auth_Reauthenticate');
      if( $viewer->getIdentity() ) {
        $session->identity = $viewer->getIdentity();
      } else if( 'cli' === PHP_SAPI ) {
        $session->identity = 'cli';
      } else {
        $session->identity = $_SERVER['REMOTE_ADDR'];
      }
      $session->start = time();
    }

    if( !empty($values['return']) ) {
      return $this->_helper->redirector->gotoUrl($return, array('prependBase' => false));
    } else {
      return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }
  }
}
