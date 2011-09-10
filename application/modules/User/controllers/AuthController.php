<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AuthController.php 8763 2011-03-31 02:14:45Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_AuthController extends Core_Controller_Action_Standard
{
  public function loginAction()
  {
    // Already logged in
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already signed in.');
      if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
        $this->_helper->redirector->gotoRoute(array(), 'home');
      }
      return;
    }

    // Make form
    $this->view->form = $form = new User_Form_Login();
    $form->populate(array(
      'return_url' => $this->_getParam('return_url'),
    ));

    // Facebook login
    if( User_Model_DbTable_Facebook::authenticate($form) ) {
      // Facebook login succeeded, redirect to home
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Not a post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }

    // Form not valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Check login creds
    extract($form->getValues()); // $email, $password, $remember
    $user_table = Engine_Api::_()->getDbtable('users', 'user');
    $user_select = $user_table->select()
      ->where('email = ?', $email);          // If post exists
    $user = $user_table->fetchRow($user_select);

    // Check if user exists
    if( empty($user) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No record of a member with that email was found.');
      $form->addError(Zend_Registry::get('Zend_Translate')->_('No record of a member with that email was found.'));

      // Register login
      Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
        'email' => $email,
        'ip' => ip2long($_SERVER['REMOTE_ADDR']),
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'state' => 'no-member',
      ));

      return;
    }

    // Handle subscriptions
    if( Engine_Api::_()->hasModuleBootstrap('payment') ) {
      // Check for the user's plan
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if( !$subscriptionsTable->check($user) ) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => ip2long($_SERVER['REMOTE_ADDR']),
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'unpaid',
        ));
        // Redirect to subscription page
        $subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
        $subscriptionSession->unsetAll();
        $subscriptionSession->user_id = $user->getIdentity();
        return $this->_helper->redirector->gotoRoute(array('module' => 'payment',
          'controller' => 'subscription', 'action' => 'index'), 'default', true);
      }
    }

    // Check if user is verified and enabled
    if( !$user->enabled ) {
      $this->view->status = false;

      $translate = Zend_Registry::get('Zend_Translate');
      $error = $translate->translate('This account still requires either email verification or admin approval.');

      if( !empty($user) && !$user->verified ) {
        $resend_url = $this->_helper->url->url(array('action' => 'resend', 'email'=>$email), 'user_signup', true);
        $error .= ' ';
        $error .= sprintf($translate->translate('Click <a href="%s">here</a> to resend the email.'), $resend_url);
      }

      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);

      // Register login
      Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
        'user_id' => $user->getIdentity(),
        'email' => $email,
        'ip' => ip2long($_SERVER['REMOTE_ADDR']),
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'state' => 'disabled',
      ));
      
      return;
    }
    
    // Run pre login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginBefore', $user);
    foreach( (array) $event->getResponses() as $response ) {
      if( is_array($response) ) {
        if( !empty($response['error']) && !empty($response['message']) ) {
          $form->addError($response['message']);
        } else if( !empty($response['redirect']) ) {
          $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
        } else {
          continue;
        }
        
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => ip2long($_SERVER['REMOTE_ADDR']),
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'third-party',
        ));

        // Return
        return;
      }
    }

    // Version 3 Import compatibility
    if( empty($user->password) ) {
      $compat = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.compatibility.password');
      $migration = null;
      try {
        $migration = Engine_Db_Table::getDefaultAdapter()->select()
          ->from('engine4_user_migration')
          ->where('user_id = ?', $user->getIdentity())
          ->limit(1)
          ->query()
          ->fetch();
      } catch( Exception $e ) {
        $migration = null;
        $compat = null;
      }
      if( !$migration ) {
        $compat = null;
      }
      
      if( $compat == 'import-version-3' ) {

        // Version 3 authentication
        $cryptedPassword = self::_version3PasswordCrypt($migration['user_password_method'], $migration['user_code'], $password);
        if( $cryptedPassword === $migration['user_password'] ) {
          // Regenerate the user password using the given password
          $user->salt = (string) rand(1000000, 9999999);
          $user->password = $password;
          $user->save();
          Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
          // @todo should we delete the old migration row?
        } else {
          $this->view->status = false;
          $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid credentials');
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
          return;
        }
        // End Version 3 authentication

      } else {
        $form->addError('There appears to be a problem logging in. Please reset your password with the Forgot Password link.');

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => ip2long($_SERVER['REMOTE_ADDR']),
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'v3-migration',
        ));
        
        return;
      }
    }

    // Normal authentication
    else {
      $authResult = Engine_Api::_()->user()->authenticate($email, $password);
      $authCode = $authResult->getCode();
      Engine_Api::_()->user()->setViewer();

      if( $authCode != Zend_Auth_Result::SUCCESS ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid credentials');
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
        
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => ip2long($_SERVER['REMOTE_ADDR']),
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'bad-password',
        ));

        return;
      }
    }

    // -- Success! --

    // Register login
    $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
    $loginTable->insert(array(
      'user_id' => $user->getIdentity(),
      'email' => $email,
      'ip' => ip2long($_SERVER['REMOTE_ADDR']),
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'state' => 'success',
      'active' => true,
    ));
    $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();
    
    // Remember
    if( $remember ) {
      $lifetime = 1209600; // Two weeks
      Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
      Zend_Session::rememberMe($lifetime);
    }

    // Increment sign-in count
    Engine_Api::_()->getDbtable('statistics', 'core')
        ->increment('user.logins');

    // Test activity @todo remove
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $viewer->lastlogin_date = date("Y-m-d H:i:s");
      if( 'cli' !== PHP_SAPI ) {
        $viewer->lastlogin_ip = ip2long($_SERVER['REMOTE_ADDR']);
      }
      $viewer->save();
      Engine_Api::_()->getDbtable('actions', 'activity')
          ->addActivity($viewer, $viewer, 'login');
    }

    // Assign sid to view for json context
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Login successful');
    $this->view->sid = Zend_Session::getId();
    $this->view->sname = Zend_Session::getOptions('name');
    
    // Run post login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginAfter', $viewer);
    
    // Do redirection only if normal context
    if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
      // Redirect by form
      $uri = $form->getValue('return_url');
      if( $uri ) {
        if( substr($uri, 0, 3) == '64-' ) {
          $uri = base64_decode(substr($uri, 3));
        }
        return $this->_redirect($uri, array('prependBase' => false));
      }

      // Redirect by session
      $session = new Zend_Session_Namespace('Redirect');
      if( isset($session->uri) ) {
        $uri  = $session->uri;
        $opts = $session->options;
        $session->unsetAll();
        return $this->_redirect($uri, $opts);
      } else if( isset($session->route) ) {
        $session->unsetAll();
        return $this->_helper->redirector->gotoRoute($session->params, $session->route, $session->reset);
      }

      // Redirect by hook
      foreach( (array) $event->getResponses() as $response ) {
        if( is_array($response) ) {
          if( !empty($response['error']) && !empty($response['message']) ) {
            return $form->addError($response['message']);
          } else if( !empty($response['redirect']) ) {
            return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
          }
        }
      }

      // Just redirect to home
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }
  }

  public function logoutAction()
  {
    // Check if already logged out
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already logged out.');
      if( null === $this->_helper->contextSwitch->getCurrentContext() )
      {
        $this->_helper->redirector->gotoRoute(array(), 'home');
      }
      return;
    }

    // Test activity @todo remove
    Engine_Api::_()->getDbtable('actions', 'activity')
        ->addActivity($viewer, $viewer, 'logout');

    $table = $this->_helper->api()->getItemTable('user');
    $onlineTable = $this->_helper->api()->getDbtable('online', 'user')
    ->delete(array(
        'user_id = ?' => $viewer->getIdentity(),
      ));

    // Facebook
    if ('none' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
      $fb_id = Engine_Api::_()->getDbtable('facebook', 'user')->find($viewer->getIdentity())->current();
      if ($fb_id && $fb_id->facebook_uid) {
        $facebook = User_Model_DbTable_Facebook::getFBInstance();
        if ($facebook->getSession()) {
          Engine_Api::_()->user()->getAuth()->clearIdentity();
          $this->_helper->redirector->gotoUrlAndExit($facebook->getLogoutUrl());
          exit;
        }
      }
    }
    
    // Logout
    Engine_Api::_()->user()->getAuth()->clearIdentity();
    
    if( !empty($_SESSION['login_id']) ) {
      Engine_Api::_()->getDbtable('logins', 'user')->update(array(
        'active' => false,
      ), array(
        'login_id = ?' => $_SESSION['login_id'],
      ));
      unset($_SESSION['login_id']);
    }



    $this->view->status = true;
    $this->view->message =  Zend_Registry::get('Zend_Translate')->_('You are now logged out.');
    if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
      return $this->_helper->redirector->gotoRouteAndExit(array(), 'home');
    }
  }

  public function forgotAction()
  {
    // no logged in users
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // Make form
    $this->view->form = $form = new User_Form_Auth_Forgot();

    // Check request
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check data
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Check for existing user
    $user = Engine_Api::_()->getItemTable('user')
      ->fetchRow(array('email = ?' => $form->getValue('email')));
    if( !$user || !$user->getIdentity() ) {
      $form->addError('A user account with that email was not found.');
      return;
    }

    // Check to make sure they're enabled
    if( !$user->enabled ) {
      $form->addError('That user account has not yet been verified or disabled by an admin.');
      return;
    }

    // Ok now we can do the fun stuff
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $db = $forgotTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Delete any existing reset password codes
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));

      // Create a new reset password code
      $code = base_convert(md5($user->salt . $user->email . $user->user_id . uniqid(time(), true)), 16, 36);
      $forgotTable->insert(array(
        'user_id' => $user->getIdentity(),
        'code' => $code,
        'creation_date' => date('Y-m-d H:i:s'),
      ));

      // Send user an email
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'core_lostpassword', array(
        'host' => $_SERVER['HTTP_HOST'],
        'email' => $user->email,
        'date' => time(),
        'recipient_title' => $user->getTitle(),
        'recipient_link' => $user->getHref(),
        'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
        'object_link' => $this->_helper->url->url(array('action' => 'reset', 'code' => $code, 'uid' => $user->getIdentity())),
        'queue' => false,
      ));

      // Show success
      $this->view->sent = true;

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function resetAction()
  {
    // no logged in users
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // Check for empty params
    $user_id = $this->_getParam('uid');
    $code = $this->_getParam('code');

    if( empty($user_id) || empty($code) ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check user
    $user = Engine_Api::_()->getItem('user', $user_id);
    if( !$user || !$user->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check code
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $forgotSelect = $forgotTable->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('code = ?', $code);
      
    $forgotRow = $forgotTable->fetchRow($forgotSelect);
    if( !$forgotRow || (int) $forgotRow->user_id !== (int) $user->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Code expired
    // Note: Let's set the current timeout for 6 hours for now
    $min_creation_date = time() - (3600 * 24);
    if( strtotime($forgotRow->creation_date) < $min_creation_date ) { // @todo The strtotime might not work exactly right
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Make form
    $this->view->form = $form = new User_Form_Auth_Reset();
    $form->setAction($this->_helper->url->url(array()));

    // Check request
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check data
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();

    // Check same password
    if( $values['password'] !== $values['password_confirm'] ) {
      $form->addError('The passwords you entered did not match.');
      return;
    }
    
    // Db
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      // Delete the lost password code now
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));
      
      // This gets handled by the post-update hook
      $user->password = $values['password'];
      $user->save();
      
      $db->commit();

      $this->view->reset = true;
      //return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function facebookSuccessAction()
  {
    $code = $this->_getParam('code');
    if ('none' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
      $form->removeElement('facebook');
    } else {
      $facebook  = User_Model_DbTable_Facebook::getFBInstance();
      if ($facebook->getSession()) {
        die("hi facebooker");
      }
    }
    
    if (!$code) {
      $this->_forward('login');
      return;
    }

    $access_token = User_Model_DbTable_Facebook::getAccessToken($code);
  }
  public function facebookSuccess2Action()
  {
    // Decode session data
    $fbSessionData = $this->_getParam('session');
    $fbSessionData = Zend_Json::decode($fbSessionData);
    
    // No data, redirect to home
    if( empty($fbSessionData) ) {
      return;
    }
    
    // Check if we have an associated user
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $select = $facebookTable->select()->where('facebook_uid = ?', $fbSessionData['uid']);
    $row = $facebookTable->fetchRow($select);

    $viewer = Engine_Api::_()->user()->getViewer();
    $user = null;
    if( null !== $row ) {
      $user = Engine_Api::_()->getItem('user', $row->user_id);
    }

    $doCookies = false;
    
    // Associated, but another user is logged in
    if( null !== $user && $viewer->getIdentity() && !$user->isSelf($viewer) ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Another member has already associated with that facebook account');
      return;
    }

    // Not associated and a user is not logged in
    if( null === $user && !$viewer->getIdentity() ) {
      // We should ideally redirect to signup
      return;
    }

    // Not associated and a user is logged in
    if( null !== $user && $viewer->getIdentity() ) {
      $doCookies = true;

      // Associate user with facebook account
      $facebookTable->insert(array(
        'user_id' => $user->getIdentity(),
        'facebook_uid' => $fbSessionData['uid'],
      ));

      return;
    }

    // Associated and not logged in
    if( null !== $user && !$viewer->getIdentity() ) {
      $doCookies = true;
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
    }

    if( $doCookies ) {
      // Get api stuff
      $config = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
      $prefix = $config['key'] . '_';

      // Set cookies
      setcookie($prefix . 'user', $fbSessionData['uid'], null, $this->view->baseUrl());
      setcookie($prefix . 'session_key', $fbSessionData['session_key'], null, $this->view->baseUrl());
      setcookie($prefix . 'expires', $fbSessionData['expires'], null, $this->view->baseUrl());
      setcookie($prefix . 'secret', $fbSessionData['secret'], null, $this->view->baseUrl());

      // Fake it
      $_COOKIE[$prefix . 'user'] = $fbSessionData['uid'];
      $_COOKIE[$prefix . 'session_key'] = $fbSessionData['session_key'];
      $_COOKIE[$prefix . 'expires'] = $fbSessionData['expires'];
      $_COOKIE[$prefix . 'secret'] = $fbSessionData['secret'];
    }


    /*
    $config = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    $fb = new Facebook_Core($config['key'], $config['secret']);
    $user_id = $fb->require_login();
    $fb->get_loggedin_user();
     * 
     */
  }

  public function facebookCancelAction()
  {
    
  }

  static protected function _version3PasswordCrypt($method, $salt, $password)
  {
    // For new methods
    if( $method > 0 ) {
      if( !empty($salt) ) {
        list($salt1, $salt2) = str_split($salt, ceil(strlen($salt) / 2));
        $salty_password = $salt1.$password.$salt2;
      } else {
        $salty_password = $password;
      }
    }

    // Hash it
    switch( $method ) {
      // crypt()
      default:
      case 0:
        $user_password_crypt = crypt($password, '$1$'.str_pad(substr($salt, 0, 8), 8, '0', STR_PAD_LEFT).'$');
      break;

      // md5()
      case 1:
        $user_password_crypt = md5($salty_password);
      break;

      // sha1()
      case 2:
        $user_password_crypt = sha1($salty_password);
      break;

      // crc32()
      case 3:
        $user_password_crypt = sprintf("%u", crc32($salty_password));
      break;
    }

    return $user_password_crypt;
  }
}
