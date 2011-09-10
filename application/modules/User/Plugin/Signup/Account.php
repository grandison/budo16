<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Account.php 8556 2011-03-04 00:53:20Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Plugin_Signup_Account extends Core_Plugin_FormSequence_Abstract
{
  protected $_name = 'account';

  protected $_formClass = 'User_Form_Signup_Account';

  protected $_script = array('signup/form/account.tpl', 'user');

  protected $_adminFormClass = 'User_Form_Admin_Signup_Account';

  protected $_adminScript = array('admin-signup/account.tpl', 'user');

  public $email = null;

  public function onView()
  {
    // Init facebook login link
    if (FALSE && 'none' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
      $facebook = User_Model_DbTable_Facebook::getFBInstance();
      if ($facebook->getSession()) {
        try {
          $me  = $facebook->api('/me');
          
          $uid = Engine_Api::_()->getDbtable('Facebook', 'User')->fetchRow(array('facebook_uid = ?'=>$facebook->getUser()));
          if ($uid)
            $uid = $uid->user_id;
          if ($uid) {
            // prevent Facebook users with established accounts from signing up again
            Engine_Api::_()->user()->getAuth()->getStorage()->write($uid);
            $this->getForm()->getElement('facebook')->setContent('<script type="text/javascript">window.location.reload();</script>"');
            return;
          } else {
            // pre-fill facebook data into signup process
            $this->getForm()->removeElement('facebook');

            if ($this->getForm()->getElement('email')->getValue() == '')
                $this->getForm()->getElement('email')->setValue($me['email']);

            if ($this->getForm()->getElement('username')->getValue() == '')
                $this->getForm()->getElement('username')->setValue(preg_replace('/[^A-Za-z]/', '', $me['name']));

            $maps    = Engine_Api::_()->fields()->getFieldsMaps('user');
            $fb_data = array();
            foreach (array('gender', 'first_name', 'last_name', 'birthdate') as $field_alias) {
              if (isset($me[$field_alias])) {
                $field    = Engine_Api::_()->fields()->getFieldsObjectsByAlias('user', $field_alias);
                $field_id = $field[$field_alias]['field_id'];
                foreach ($maps as $map) {
                  if ($field_id == $map->child_id) {
                    $fb_data[$map->getKey()] = $me[$field_alias];
                  }
                }
              }
            }
            $this->getSession()->data = $fb_data;
          }
        } catch (Exception $e) { 
          $this->getForm()->removeElement('facebook');
        }
      }
    }
  }
  
  public function onProcess()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $random = ($settings->getSetting('user.signup.random', 0) == 1);
    $data = $this->getSession()->data;

    // Add email and code to invite session if available
    $inviteSession = new Zend_Session_Namespace('invite');
    if( isset($data['email']) ) {
      $inviteSession->signup_email = $data['email'];
    }
    if( isset($data['code']) ) {
      $inviteSession->signup_code = $data['code'];
    }

    if( $random ) {
      $data['password'] = Engine_Api::_()->user()->randomPass(10);
    }

    // Create user
    // Note: you must assign this to the registry before calling save or it
    // will not be available to the plugin in the hook
    $this->_registry->user = $user = Engine_Api::_()->getDbtable('users', 'user')->createRow();
    $user->setFromArray($data);
    $user->save();
    
    Engine_Api::_()->user()->setViewer($user);

    // Increment signup counter
    Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.creations');
    
    if( $user->verified && $user->enabled ) {
      // Create activity for them
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'signup');
      // Set user as logged in if not have to verify email
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
    }

    $mailType = null;
    $mailParams = array(
      'host' => $_SERVER['HTTP_HOST'],
      'email' => $user->email,
      'date' => time(),
      'recipient_title' => $user->getTitle(),
      'recipient_link' => $user->getHref(),
      'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
      'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
    );

    // Add password to email if necessary
    if( $random ) {
      $mailParams['password'] = $data['password'];
    }

    // Mail stuff
    switch( $settings->getSetting('user.signup.verifyemail', 0) ) {
      case 0:
        // only override admin setting if random passwords are being created
        if( $random ) {
          $mailType = 'core_welcome_password';
        }
        break;

      case 1:
        // send welcome email
        $mailType = ($random ? 'core_welcome_password' : 'core_welcome');
        break;

      case 2:
        // verify email before enabling account
        $verify_table = Engine_Api::_()->getDbtable('verify', 'user');
        $verify_row = $verify_table->createRow();
        $verify_row->user_id = $user->getIdentity();
        $verify_row->code = md5($user->email
            . $user->creation_date
            . $settings->getSetting('core.secret', 'staticSalt')
            . (string) rand(1000000, 9999999));
        $verify_row->date = $user->creation_date;
        $verify_row->save();
        
        $mailType = ($random ? 'core_verification_password' : 'core_verification');
        
        $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
              'action' => 'verify',
              'email' => $user->email,
              'verify' => $verify_row->code
            ), 'user_signup', true);
        break;

      default:
        // do nothing
        break;
    }
    
    if( $mailType ) {
      Engine_Api::_()->getApi('mail', 'core')->sendSystem(
        $user,
        $mailType,
        $mailParams
      );
    }
  }

  public function onAdminProcess($form)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $values = $form->getValues();
    $settings->user_signup = $values;
    if( $values['inviteonly'] == 1 ) {
      $step_table = Engine_Api::_()->getDbtable('signup', 'user');
      $step_row = $step_table->fetchRow($step_table->select()->where('class = ?', 'User_Plugin_Signup_Invite'));
      $step_row->enable = 0;
    }

    $form->addNotice('Your changes have been saved.');
  }

}