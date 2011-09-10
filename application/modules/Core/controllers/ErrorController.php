<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ErrorController.php 8945 2011-05-18 00:28:38Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_ErrorController extends Core_Controller_Action_Standard
{
  public function errorAction()
  {
    $error = $this->_getParam('error_handler');
    $this->view->error_code = $error_code = Engine_Api::getErrorCode(true);

    // Handle missing pages
    switch( $error->type ) {
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        return $this->_forward('notfound');
        break;

      default:
        break;
    }
    
    // Log this message
    if( isset($error->exception) &&
        Zend_Registry::isRegistered('Zend_Log') &&
        ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log ) {
      // Only log if in production or the exception is not an instance of Engine_Exception
      $e = $error->exception;
      if( 'production' === APPLICATION_ENV || !($e instanceof Engine_Exception) ) {
        $output = '';
        $output .= PHP_EOL . 'Error Code: ' . $error_code . PHP_EOL;
        $output .= $e->__toString();
        $log->log($output, Zend_Log::CRIT);
      }
    }
    
    //$this->getResponse()->setRawHeader('HTTP/1.1 500 Internal server error');
    $this->view->status = false;
    $this->view->errorName = get_class($error->exception);

    if( APPLICATION_ENV != 'production' ) {
      $this->view->error = $error->exception->__toString();
    } else {
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('An error has occurred');
    }
  }

  public function notfoundAction()
  {
    // 404 error -- controller or action not found
    $this->getResponse()->setRawHeader($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    $this->view->status = false;
    $this->view->error = Zend_Registry::get('Zend_Translate')->_('The requested resource could not be found.');
  }

  public function requiresubjectAction()
  {
    return $this->_forward('notfound');
    
    // 404 error -- subject not found
    $this->getResponse()->setRawHeader($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    $this->view->status = false;
    $this->view->error = Zend_Registry::get('Zend_Translate')->_('The requested resource could not be found.');
  }

  public function requireauthAction()
  {
    // 403 error -- authorization failed
    if( !$this->_helper->requireUser()->isValid() ) return;
    $this->getResponse()->setRawHeader($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    $this->view->status = false;
    $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are not authorized to access this resource.');
  }

  public function requireuserAction()
  {
    // 403 error -- authorization failed
    $this->getResponse()->setRawHeader($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    $this->view->status = false;
    $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are not authorized to access this resource.');

    // Show the login form for them :P
    $this->view->form = $form = new User_Form_Login();
    $form->addError('Please sign in to continue..');
    $form->return_url->setValue(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // Facebook login
    if (User_Model_DbTable_Facebook::authenticate($form))
      // Facebook login succeeded, redirect to home
      $this->_helper->redirector->gotoRoute(array(), 'home');
  }

  public function requireadminAction()
  {
    // Should probably make this do something else later
    //$this->_helper->layout->setLayout('admin');
    return $this->_forward('notfound');
  }
}