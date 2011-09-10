<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Room.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_Room extends Engine_Db_Table_Row
{
  protected $_roomUserIds;

  public function toRemoteArray()
  {
    $return = array(
      'type' => 'room',
      'identity' => $this->room_id,
      'title' => $this->title,
      'public' => $this->public,
      'people' => $this->user_count
    );

    return $return;
  }
  
  public function join(User_Model_User $user)
  {
    // Already joined
    if( $this->hasUser($user) ) {
      throw new Chat_Model_Exception('ALREADY_JOINED');
    }

    // Create room user
    $roomUserTable = Engine_Api::_()->getDbtable('RoomUsers', 'chat');
    $roomUser = $roomUserTable->createRow()->setFromArray(array(
      'room_id' => $this->room_id,
      'user_id' => $user->getIdentity(),
      'date' => date('Y-m-d H:i:s'),
      'state' => 1
    ))->save();

    // Clear local cache
    $this->_roomUserIds = null;
    // Add to local cache
    //$this->_roomUserIds[] = $user->getIdentity();

    return $roomUser;
  }

  public function leave(User_Model_User $user)
  {
    // Already left
    $roomUser = $this->getUser($user);
    if( null === $roomUser ) {
      throw new Chat_Model_Exception('ALREADY_LEFT');
    }

    // Delete room user
    $roomUser->delete();

    // Flush local cache
    $this->_roomUserIds = null;

    return $this;
  }

  public function send(User_Model_User $user, $body)
  {
    // Must be joined
    $roomUser = $this->getUser($user);
    if( null === $roomUser ) {
      throw new Chat_Model_Exception('NOT_IN_ROOM');
    }

    // Command processing
    if( strpos($body, '/') === 0 ) {
      $ret = $this->_processCommand($user, $body);
      if( $ret ) {
        return $ret;
      }
    }
    
    // Send
    $messageTable = Engine_Api::_()->getDbtable('messages', 'chat');
    $message = $messageTable->createRow();
    $message->setRoom($this)->setFromArray(array(
      'user_id' => $user->user_id,
      'room_id' => $this->room_id,
      'body' => $body,
      'date' => date('Y-m-d H:i:s'),
    ))->save();

    return $message;
  }



  public function hasUser(User_Model_User $user)
  {
    return (null !== $this->getUser($user) );
  }

  public function getUser(User_Model_User $user)
  {
    return Engine_Api::_()->getDbtable('RoomUsers', 'chat')->find($this->room_id, $user->getIdentity())->current();
  }

  public function getUserIds()
  {
    //if( null === $this->_roomUserIds ) {
      $roomUserTable = Engine_Api::_()->getDbtable('RoomUsers', 'chat');
      $roomUserSelect = $roomUserTable->select()
        ->where('room_id = ?', $this->room_id);

      $ids = array();
      foreach( $roomUserTable->fetchAll($roomUserSelect) as $row ) {
        $ids[] = $row->user_id;
      }
      $this->_roomUserIds = $ids;
    //}

    return $this->_roomUserIds;
  }

  public function getUsers()
  {
    return Engine_Api::_()->getItemMulti('user', $this->getUserIds());
  }



  protected function _processCommand($user, $body) {
    list($command, $body) = explode(' ', $body, 2);
    $command = trim($command, ' /\\');
    switch( $command ) {
      case 'me':
        // Send system message
        $body = $user->getTitle() . ' ' . $body;
        $messageTable = Engine_Api::_()->getDbtable('messages', 'chat');
        $message = $messageTable->createRow();
        $message->setRoom($this)->setFromArray(array(
          'user_id' => $user->user_id,
          'room_id' => $this->room_id,
          'body' => $body,
          'date' => date('Y-m-d H:i:s'),
          'system' => 1
        ))->save();
        return $message;
        break;
      case 'online':
        
        break;
    }
  }
}