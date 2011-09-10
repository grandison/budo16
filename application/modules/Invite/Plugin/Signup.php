<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Signup.php 8496 2011-02-18 21:53:03Z jung $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Invite_Plugin_Signup
{
  public function onUserCreateAfter($payload)
  {
    $user = $payload->getPayload();
    $session = new Zend_Session_Namespace('invite');
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $isEligible = Engine_Api::_()->getApi('settings', 'core')->user_friends_eligible;
    //$inviteTable = new Zend_Db_Table();

    // Get codes
    $codes = array();
    if( !empty($session->invite_code) ) {
      $codes[] = $session->invite_code;
    }
    if( !empty($session->signup_code) ) {
      $codes[] = $session->signup_code;
    }
    $codes = array_unique($codes);

    // Get emails
    $emails = array();
    if( !empty($session->invite_email) ) {
      $emails[] = $session->invite_email;
    }
    if( !empty($session->signup_email) ) {
      $emails[] = $session->signup_email;
    }
    $emails = array_unique($emails);

    // Nothing, exit now
    if( empty($codes) && empty($emails) ) {
      return;
    }
    
    // Get related invites
    $select = $inviteTable->select();

    if( !empty($codes) ) {
      $select->orWhere('code IN(?)', $codes);
    }

    if( !empty($emails) ) {
      $select->orWhere('recipient IN(?)', $emails);
    }
    
    $updateInviteIds = array();
    $befriendUserIds = array();
    foreach( $inviteTable->fetchAll($select) as $invite ) {
      $befriendUserIds[] = $invite->user_id;

      // Set new user if if not already
      if( 0 == $invite->new_user_id ) {
        $updateInviteIds[] = $invite->id;
      }
    }

    // Update invites
    if( !empty($updateInviteIds) ) {
      $inviteTable->update(array(
        'new_user_id' => $user->getIdentity(),
      ), array(
        'id IN(?)' => $updateInviteIds,
        'new_user_id = ?' => 0,
      ));
    }
    
    // Befriend users
    if( $isEligible && !empty($befriendUserIds) ) {
      $befriendUsers = Engine_Api::_()->getItemTable('user')->find($befriendUserIds);
      if( !empty($befriendUsers) ) {
        $activity = Engine_Api::_()->getDbtable('notifications', 'activity');
        foreach( $befriendUsers as $befriendUser ) {
          $user->membership()
            ->addMember($befriendUser)
            ->setUserApproved($befriendUser);

          // Handle notification depending on the newtork friendship settings
          $this->_handleNotification($user, $befriendUser);
        }
      }
    }


    // Clean session
    $session->unsetAll();
  }

  public function _handleNotification($user, $befriendUser){
    // if one way friendship and verification not required
    if(!$user->membership()->isUserApprovalRequired()&&!$user->membership()->isReciprocal()){
      // Add activity
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($befriendUser, $user, 'friends_follow', '{item:$object} is now following {item:$subject}.');

      // Add notification
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $befriendUser, 'friend_follow');

      $message = Zend_Registry::get('Zend_Translate')->_("You are now following this member.");
    }

    // if two way friendship and verification not required
    else if(!$user->membership()->isUserApprovalRequired()&&$user->membership()->isReciprocal()){
      // Add activity
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $befriendUser, 'friends', '{item:$object} is now friends with {item:$subject}.');
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($befriendUser, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');

      // Add notification
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_accepted');
      $message = Zend_Registry::get('Zend_Translate')->_("You are now friends with this member.");
    }

    // if one way friendship and verification required
    else if(!$user->membership()->isReciprocal()){
      // Add notification
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_follow_request');
      $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
    }

    // if two way friendship and verification required
    else if($user->membership()->isReciprocal())
    {
      // Add notification
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_request');
      $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
    }

  }
  
}

