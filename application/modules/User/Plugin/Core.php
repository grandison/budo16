<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8572 2011-03-05 18:43:15Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Plugin_Core
{
  public function onUserDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {

      // Remove from online users
      $onlineUsersTable = Engine_Api::_()->getDbtable('online', 'user');
      $onlineUsersTable->delete(array(
        'user_id = ?' => $payload->getIdentity(),
      ));

      // Remove friends
      $payload->membership()->removeAllUserFriendship();

      // Remove all cases user is in a friend list
      $payload->lists()->removeUserFromLists();

      // Remove all friend list created by the user
      $payload->lists()->removeUserLists();
    }
  }

  public function onUserEnable($event)
  {
    $user = $event->getPayload();
    if( !($user instanceof User_Model_User) ) {
      return;
    }

    // update networks
    Engine_Api::_()->network()->recalculate($user);

      // Create activity for them if it doesn't exist
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    $signupActionIdentity = $actionTable->select()
      ->from($actionTable, 'action_id')
      ->where('type = ?', 'signup')
      ->where('subject_type = ?', $user->getType())
      ->where('subject_id = ?', $user->getIdentity())
      ->query()
      ->fetchColumn();
    if( !$signupActionIdentity ) {
      $actionTable->addActivity($user, $user, 'signup');
    }
    
//    try {
//      // Send welcome email?
//      Engine_Api::_()->getApi('mail', 'core')->sendSystem(
//        $user,
//        'core_welcome',
//        array(
//          'host' => $_SERVER['HTTP_HOST'],
//          'email' => $user->email,
//          'date' => time(),
//          'recipient_title' => $user->getTitle(),
//          'recipient_link' => $user->getHref(),
//          'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
//          'object_link' => 'http://'
//            . $_SERVER['HTTP_HOST']
//            . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
//        )
//      );
//    } catch( Exception $e ) {}
  }
  
  public function getAdminNotifications($event)
  {
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = new Zend_Db_Select($userTable->getAdapter());
    $select->from($userTable->info('name'), 'COUNT(user_id) as count')
      ->where('enabled = ?', 0)
      ;

    $data = $select->query()->fetch();
    if( empty($data['count']) ) {
      return;
    }

    $translate = Zend_Registry::get('Zend_Translate');
    $message = vsprintf($translate->translate(array(
      'There is <a href="%s">%d new member</a> awaiting your approval.',
      'There are <a href="%s">%d new members</a> awaiting your approval.',
      $data['count']
    )), array(
      Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'manage'), 'admin_default', true) . '?enabled=0',
      $data['count'],
    ));

    $event->addResponse($message);
  }

  public function onUserCreateAfter($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
      if( 'none' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable ){
        $facebook = User_Model_DbTable_Facebook::getFBInstance();
        if ($facebook->getSession()) {
          try {
            $facebook->api('/me');
            $table = Engine_Api::_()->getDbtable('facebook', 'user');
            $row = $table->fetchRow(array('user_id = ?'=>$payload->getIdentity()));
            if (!$row) {
              $row = Engine_Api::_()->getDbtable('facebook', 'user')->createRow();
              $row->user_id = $payload->getIdentity();
            }
            $row->facebook_uid = $facebook->getUser();
            $row->save();
          } catch (Exception $e) {}
        }
      }
    
      // Set default email notifications
      $notificationTypesTable = Engine_Api::_()->getDbtable('notificationTypes', 'activity');
      
      // For backwards compatiblitiy this block will only execute if the 
      // getDefaultNotifications function exists. If notifications aren't 
      // being added to the engine4_activity_notificationsettings table
      // check to see if the Activity_Model_DbTable_NotificationTypes class
      // is out of date
      if( method_exists($notificationTypesTable, 'getDefaultNotifications') ){
        $defaultNotifications = $notificationTypesTable->getDefaultNotifications();
        
        Engine_Api::_()->getDbtable('notificationSettings', 'activity')
          ->setEnabledNotifications($payload, $defaultNotifications);
      }
    }
  }

}