<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AjaxController.php 8332 2011-01-29 00:55:33Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_AjaxController extends Core_Controller_Action_Standard
{
  public function pingAction()
  {
    $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $roomUserTable = Engine_Api::_()->getDbtable('RoomUsers', 'chat');
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');

    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $user = $userTable->check($viewer);
    $user->setUser($viewer);

    // Check for room users
    $roomUserTable->check($viewer, $this->_getParam('rooms'));

    //$db = $eventTable->getAdapter();
    //$db->beginTransaction();

    // Now get all events
    //$ts = $this->_getParam('ts', time());
    $lastEventTime = $prevLastEventTime = $this->_getParam('lastEventTime', time());
    
    $events = array();
    foreach( $eventTable->getEvents($viewer, $prevLastEventTime) as $event ) {
      $events[$event->event_id] = $event->toRemoteArray();
      $lastEventTime = strtotime($event->date);
    }
    $this->view->lastEventTime = $lastEventTime;
    $this->view->events = $events;
    
    // If sending "fresh" parameter, load other stuff too
    $fresh = $this->_getParam('fresh', false);
    if( $fresh && $fresh != 'false' ) {
      // Like online friends
      $users = array();
      // Add viewer
      $users[$viewer->getIdentity()] = array(
        'identity' => $viewer->getIdentity(),
        'title' => $viewer->getTitle(),
        'href' => $viewer->getHref(),
        'photo' => $viewer->getPhotoUrl('thumb.icon'),
        'state' => 1,
        'self' => 1
      );
      foreach( Engine_Api::_()->getItemMulti('user', $user->getUsersToBeNotifiedOfPresence()) as $friend ) {
        $users[$friend->getIdentity()] = array(
          'identity' => $friend->getIdentity(),
          'title' => $friend->getTitle(),
          'href' => $friend->getHref(),
          'photo' => $friend->getPhotoUrl('thumb.icon'),
          'state' => 1,
          'self' => 0
        );
      }
      $this->view->users = $users;
      // And whispers
      $whispers = array();
      foreach( $user->getStaleWhispers() as $whisper ) {
        // Get user if not online
        $whipserData = $whisper->toRemoteArray();
        $whipserData['stale'] = 1;
        $whispers[$whisper->whisper_id] = $whipserData;
        if( !isset($this->view->users[$whipserData['user_id']]) ) {
          $whisperUser = Engine_Api::_()->getItem('user', $whipserData['user_id']);
          if( $whisperUser && isset($whisperUser->user_id) ) {
            $this->view->users[$whipserData['user_id']] = array(
              'type' => 'presence',
              'identity' => $whisperUser->user_id,
              'title' => $whisperUser->getTitle(),
              'href' => $whisperUser->getHref(),
              'photo' => $whisperUser->getPhotoUrl('thumb.icon'),
              'state' => 0,
            );
          }
        }
      }
      $this->view->whispers = $whispers;
    }

    Engine_Api::_()->getDbtable('events', 'chat')->gc();
    Engine_Api::_()->getDbtable('users', 'chat')->gc();
    Engine_Api::_()->getDbtable('roomusers', 'chat')->gc();
    Engine_Api::_()->getDbtable('whispers', 'chat')->gc();
  }
  
  public function createAction()
  {
    
  }
  
  public function joinAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');

    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $userTable->check($viewer);
    
    // Check room id
    $room_id = $this->_getParam('room_id');
    $room = $roomTable->find($room_id)->current();
    if( null === $room ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'MISSING_ROOM';
      return;
    }
    
    // Start transaction
    $db = $roomTable->getAdapter();
    $db->beginTransaction();

    try
    {
      $room->join($viewer);
      
      $db->commit();

      $this->view->status = true;
      $this->view->error = false;
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'ERROR';
      if( APPLICATION_ENV === 'development' ) {
        $this->view->error_message = $e->__toString();
      }
    }

    // In either case, send back room info
    $roomUsers = array();
    $roomUsers[$viewer->getIdentity()] = array(
        'type' => 'grouppresence',
        'identity' => $viewer->getIdentity(),
        'title' => $viewer->getTitle(),
        'href' => $viewer->getHref(),
        'photo' => $viewer->getPhotoUrl('thumb.icon'),
        'stale' => true,
        'state' => 1,
        'self' => 1,
        'room_id' => $room->room_id
    );
    foreach( $room->getUsers() as $user ) {
      if( $user->getIdentity() == $viewer->getIdentity() ) continue;
      $roomUsers[$user->getIdentity()] = array(
        'type' => 'grouppresence',
        'identity' => $user->getIdentity(),
        'title' => $user->getTitle(),
        'href' => $user->getHref(),
        'photo' => $user->getPhotoUrl('thumb.icon'),
        'stale' => true,
        'state' => 1,
        'self' => 0,
        'room_id' => $room->room_id
      );
    }

    $this->view->room = $room->toArray();
    $this->view->users = $roomUsers;
  }

  public function leaveAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');
    
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $userTable->check($viewer);
    
    // Check room id
    $room_id = $this->_getParam('room_id');
    $room = $roomTable->find($room_id)->current();
    if( null === $room ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'MISSING_ROOM';
      return;
    }
    
    // Start transaction
    $db = $roomTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Remove room user
      $room->leave($viewer);
      
      $db->commit();

      $this->view->status = true;
      $this->view->error = false;
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'ERROR';
      if( APPLICATION_ENV === 'development' ) {
        $this->view->error_message = $e->__toString();
      }
    }
  }

  public function sendAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');
    
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $userTable->check($viewer);
    
    // Check room id
    $room_id = $this->_getParam('room_id');
    $room = $roomTable->find($room_id)->current();
    if( null === $room ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'MISSING_ROOM';
      return;
    }
    
    // Check for empty message
    $censor  = new Engine_Filter_Censor();
    $message = $censor->filter( $this->_getParam('message') );
    if( empty($message) ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'EMPTY_MESSAGE';
      return;
    }

    $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
    $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_NOQUOTES, 'UTF-8');
    if( Engine_String::strlen($message) > 1023 ) {
      $message = Engine_String::substr($message, 0, 1023);
    }

    // Rate limiting
    $session = $this->getSession();

    // Clear out old
    if( !isset($session->rate) || !is_array($session->rate) ) $session->rate = array();
    foreach( $session->rate as $index => $time ) {
      if( time() > $time + 5 ) {
        unset($session->rate[$index]);
      }
    }

    // Check count
    $rate = count($session->rate);
    if( $rate > 10 ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'RATE_LIMIT_EXCEEDED';
      return;
    }

    // Start transaction
    $db = $roomTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Send message
      $messageObject = $room->send($viewer, $message);

      $session->rate[] = time();

      $db->commit();

      $this->view->status = true;
      $this->view->error = false;
      if( isset($messageObject) && is_object($messageObject) ) {
        $this->view->message_id = $messageObject->message_id;
      }
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'ERROR';
      if( APPLICATION_ENV === 'development' ) {
        $this->view->error_message = $e->__toString();
      }
    }
  }

  public function whisperAction()
  {
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');
    
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $userTable->check($viewer);

    // Check for target user
    $targetUserId = (int) $this->_getParam('user_id');
    $targetUser = $userTable->find($targetUserId)->current();
    if( null === $targetUser ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'NOT_ONLINE';
      return;
    }

    // Rate limiting
    $session = $this->getSession();

    // Clear out old
    if( !isset($session->whisperRate) || !is_array($session->whisperRate) ) $session->whisperRate = array();
    foreach( $session->whisperRate as $index => $time ) {
      if( time() > $time + 5 ) {
        unset($session->whisperRate[$index]);
      }
    }

    // Check count
    $rate = count($session->whisperRate);
    if( $rate > 10 ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'RATE_LIMIT_EXCEEDED';
      return;
    }

    // Do it!
    $censor  = new Engine_Filter_Censor();
    $message = $censor->filter( $this->_getParam('message') );
    $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
    $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_NOQUOTES, 'UTF-8');
    
    if( Engine_String::strlen($message) > 1023 ) {
      $message = Engine_String::substr($message, 0, 1023);
    }
    
    // Start transaction
    $db = $userTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Send message
      $whisperObject = $targetUser->whisper($viewer, $message);
      
      $session->whisperRate[] = time();

      $db->commit();

      $this->view->status = true;
      $this->view->error = false;
      $this->view->whisper_id = $whisperObject->whisper_id;
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'ERROR';
      if( APPLICATION_ENV === 'development' ) {
        $this->view->error_message = $e->__toString();
      }
    }
  }


  public function whisperCloseAction()
  {
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');

    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $userTable->check($viewer);

    // Do it!
    $whisperTable = Engine_Api::_()->getDbtable('whispers', 'chat');
    $other_user_id = $this->_getParam('user_id');

    // Start transaction
    $db = $userTable->getAdapter();
    $db->beginTransaction();

    try
    {
      $whisperTable->closeConversation($viewer, $other_user_id);
      
      $db->commit();

      $this->view->status = true;
      $this->view->error = false;
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'ERROR';
      if( APPLICATION_ENV === 'development' ) {
        $this->view->error_message = $e->__toString();
      }
    }
  }


  public function statusAction()
  {
    $userTable = Engine_Api::_()->getDbtable('users', 'chat');

    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'AUTH_FAIL';
      return;
    }

    // Check for chat user
    $chatUser = $userTable->check($viewer);

    // Validate state
    $state = (int) $this->_getParam('status', 1);
    if( !in_array($state, array(0, 1, 2)) ) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = 'UNKNOWN_STATE';
      return;
    }

    $this->view->type = $type = $this->_getParam('type');

    // Set state
    if( null === $type || 'im' === $type ) {
      $this->view->im = true;
      if( $chatUser->state != 0 || $state != 2 ) {
        $chatUser->state = $state;
        $chatUser->save();
      }
    }

    // Do the same for all rooms
    if( null === $type || 'chat' === $type ) {
      $this->view->chat = true;
      $roomUserTable = Engine_Api::_()->getDbtable('RoomUsers', 'chat');
      $select = $roomUserTable->select()->where('user_id = ?', $viewer->getIdentity());
      foreach( $roomUserTable->fetchAll($select) as $roomUser ) {
        $roomUser->state = $state;
        $roomUser->save();
      }
    }
    
    $this->view->status = true;
    $this->view->error = false;
  }

  public function listAction()
  {
    // Get rooms
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $select = $roomTable->select()
      ->where('public = ?', 1)
      ->order('title ASC');

    $rooms = array();
    foreach( $roomTable->fetchAll($select) as $room ) {
      $rooms[$room->room_id] = $room->toRemoteArray();
    }
    $this->view->rooms = $rooms;
  }
}