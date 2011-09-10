<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: SignupController.php 8927 2011-05-10 02:05:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_SignupController extends Core_Controller_Action_Standard
{
  public function init()
  {
  }
  
  public function indexAction()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');

    // If the user is logged in, they can't sign up now can they?
    if( Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    $formSequenceHelper = $this->_helper->formSequence;
    foreach( $this->_helper->api()->getDbtable('signup', 'user')->fetchAll() as $row )
    {
      if( $row->enable == 1 ) {
        $class = $row->class;
        $formSequenceHelper->setPlugin(new $class, $row->order);
      }
    }

    // This will handle everything until done, where it will return true
    if( !$this->_helper->formSequence() ) {
      return;
    }

    // Get viewer
    $viewer = Engine_Api::_()->user()->getViewer();

    // Run post signup hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserSignupAfter', $viewer);
    $responses = $event->getResponses();
    if( $responses ){
      foreach( $event->getResponses() as $response ) {
        if( is_array($response) ) {
          // Clear login status
          if( !empty($response['error']) ) {
            Engine_Api::_()->user()->setViewer(null);
            Engine_Api::_()->user()->getAuth()->getStorage()->clear();
          }
          // Redirect
          if( !empty($response['redirect']) ) {
            return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
          }
        }
      }
    }
    
    // Handle subscriptions
    if( Engine_Api::_()->hasModuleBootstrap('payment') ) {
      // Check for the user's plan
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if( !$subscriptionsTable->check($viewer) ) {
        // Redirect to subscription page, log the user out, and set the user id
        // in the payment session
        $subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
        $subscriptionSession->user_id = $viewer->getIdentity();
        
        Engine_Api::_()->user()->setViewer(null);
        Engine_Api::_()->user()->getAuth()->getStorage()->clear();

        if( !empty($subscriptionSession->subscription_id) ) {
          return $this->_helper->redirector->gotoRoute(array('module' => 'payment',
            'controller' => 'subscription', 'action' => 'gateway'), 'default', true);
        } else {
          return $this->_helper->redirector->gotoRoute(array('module' => 'payment',
            'controller' => 'subscription', 'action' => 'index'), 'default', true);
        }
      }
    }

    // Handle email verification or pending approval
    if( !$viewer->enabled ) {
      Engine_Api::_()->user()->setViewer(null);
      Engine_Api::_()->user()->getAuth()->getStorage()->clear();

      $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
      $confirmSession->approved = $viewer->approved;
      $confirmSession->verified = $viewer->verified;
      $confirmSession->enabled  = $viewer->enabled;
      return $this->_helper->_redirector->gotoRoute(array('action' => 'confirm'), 'user_signup', true);
    }

    // Handle normal signup
    else {
      Engine_Hooks_Dispatcher::getInstance()
          ->callEvent('onUserEnable', $viewer);
    }

    // Set lastlogin_date here to prevent issues with payment
    if( $viewer->getIdentity() ) {
      $viewer->lastlogin_date = date("Y-m-d H:i:s");
      if( 'cli' !== PHP_SAPI ) {
        $viewer->lastlogin_ip = ip2long($_SERVER['REMOTE_ADDR']);
      }
      $viewer->save();
    }

    return $this->_helper->_redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
  }

  public function verifyAction()
  {
    $verify = $this->_getParam('verify');
    $email = $this->_getParam('email');
    $settings = Engine_Api::_()->getApi('settings', 'core');

    // No code or email
    if( !$verify || !$email ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('The email or verification code was not valid.');
      return;
    }

    // Get verify user
    $userTable = Engine_Api::_()->getDbtable('users', 'user');
    $user = $userTable->fetchRow($userTable->select()->where('email = ?', $email));

    if( !$user || !$user->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('The email does not match an existing user.');
      return;
    }

    // If the user is already verified, just redirect
    if( $user->verified ) {
      $this->view->status = true;
      return;
    }

    // Get verify row
    $verifyTable = Engine_Api::_()->getDbtable('verify', 'user');
    $verifyRow = $verifyTable->fetchRow($verifyTable->select()->where('user_id = ?', $user->getIdentity()));

    if( !$verifyRow || $verifyRow->code != $verify ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('There is no verification info for that user.');
      return;
    }
    
    // Process
    $db = $verifyTable->getAdapter();
    $db->beginTransaction();

    try {

      $verifyRow->delete();
      $user->verified = 1;
      $user->save();

      if( $user->enabled ) {
        Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserEnable', $user);
      }

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
  }

  public function takenAction()
  {
    $username = $this->_getParam('username');
    $email = $this->_getParam('email');

    // Sent both or neither username/email
    if( (bool) $username == (bool) $email )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param count');
      return;
    }

    // Username must be alnum
    if( $username ) {
      $validator = new Zend_Validate_Alnum();
      if( !$validator->isValid($username) )
      {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param value');
        //$this->view->errors = $validator->getErrors();
        return;
      }

      $table = Engine_Api::_()->getItemTable('user');
      $row = $table->fetchRow($table->select()->where('username = ?', $username)->limit(1));

      $this->view->status = true;
      $this->view->taken = ( $row !== null );
      return;
    }

    if( $email ) {
      $validator = new Zend_Validate_EmailAddress();
      if( !$validator->isValid($email) )
      {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param value');
        //$this->view->errors = $validator->getErrors();
        return;
      }

      $table = Engine_Api::_()->getItemTable('user');
      $row = $table->fetchRow($table->select()->where('email = ?', $email)->limit(1));

      $this->view->status = true;
      $this->view->taken = ( $row !== null );
      return;
    }
  }

  public function confirmAction()
  {
    $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
    $this->view->approved = $this->_getParam('approved', $confirmSession->approved);
    $this->view->verified = $this->_getParam('verified', $confirmSession->verified);
    $this->view->enabled  = $this->_getParam('verified', $confirmSession->enabled);
  }


  public function resendAction()
  {
    $email = $this->_getParam('email');
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() || !$email ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    $userTable = Engine_Api::_()->getDbtable('users', 'user');
    $user = $userTable->fetchRow($userTable->select()->where('email = ?', $email));
    
    if( !$user ) {
      $this->view->error = 'That email was not found in our records.';
      return;
    }
    if( $user->verified ) {
      $this->view->error = 'That email has already been verified. You may now login.';
      return;
    }
    
    // resend verify email
    $verifyTable = Engine_Api::_()->getDbtable('verify', 'user');
    $verifyRow = $verifyTable->fetchRow($verifyTable->select()->where('user_id = ?', $user->user_id)->limit(1));
    
    if( !$verifyRow ) {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $verifyRow = $verifyTable->createRow();
      $verifyRow->user_id = $user->getIdentity();
      $verifyRow->code = md5($user->email
          . $user->creation_date
          . $settings->getSetting('core.secret', 'staticSalt')
          . (string) rand(1000000, 9999999));
      $verifyRow->date = $user->creation_date;
      $verifyRow->save();
    }
    
    $mailParams = array(
      'host' => $_SERVER['HTTP_HOST'],
      'email' => $user->email,
      'date' => time(),
      'recipient_title' => $user->getTitle(),
      'recipient_link' => $user->getHref(),
      'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
      'queue' => false,
    );
    
    $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'action' => 'verify',
          //'email' => $email,
          //'verify' => $verifyRow->code
        ), 'user_signup', true)
      . '?'
      . http_build_query(array('email' => $email, 'verify' => $verifyRow->code))
      ;
    
    Engine_Api::_()->getApi('mail', 'core')->sendSystem(
      $user,
      'core_verification',
      $mailParams
    );
  }
}