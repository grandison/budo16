<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: User.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_User extends Engine_Db_Table_Row
{
  protected $_user;

  protected $_presenceUsers;

  public function toRemoteArray()
  {
    $user = $this->getUser();
    $return = array(
      'identity' => $user->getIdentity(),
      'title' => $user->getTitle(),
      'href' => $user->getHref(),
      'photo' => $user->getPhotoUrl('thumb.icon'),
      'state' => $this->state,
    );

    return $return;
  }

  public function whisper(User_Model_User $user, $body)
  {
    $ids = $this->getUsersToBeNotifiedOfPresence();
    if( !in_array($user->user_id, $ids) ) {
      throw new Chat_Model_Exception('NOT_BUDDY_BUDDY' . ' ' . join(', ', $this->getFriendIdsOfUser()));
    }

    $whisperTable = Engine_Api::_()->getDbtable('whispers', 'chat');
    $whisper = $whisperTable->createRow();
    $whisper->setFromArray(array(
      'recipient_id' => $this->user_id,
      'sender_id' => $user->user_id,
      'body' => $body,
      'date' => date('Y-m-d H:i:s'),
    ))->save();

    return $whisper;
  }


  
  public function setUser(User_Model_User $user)
  {
    $this->_user = $user;
    return $this;
  }

  public function getUser()
  {
    if( null === $this->_user ) {
      $this->_user = Engine_Api::_()->getItem('user', $this->user_id);
    }

    return $this->_user;
  }

  public function getRooms()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    return $roomTable->find($this->getRoomIds());
  }

  public function getRoomIds()
  {
    $ids = array();
    foreach( $this->getRoomUsers() as $row ) {
      $ids[] = $row->room_id;
    }

    return $ids;
  }

  public function getRoomUsers()
  {
    $roomUserTable = Engine_Api::_()->getDbtable('RoomUsers', 'chat');
    $roomUserSelect = $roomUserTable->select()
      ->where('user_id = ?', $this->user_id);
    return $roomUserTable->fetchAll($roomUserSelect);
  }

  public function getStaleWhispers()
  {
    return Engine_Api::_()->getDbtable('whispers', 'chat')->getStaleWhispers($this->getUser());
  }




  // Presence data

  public function getUsersToBeNotifiedOfPresence()
  {
    // Local cache
    if( isset($this->_presenceUsers) ) {
      return $this->_presenceUsers;
    }

    $conf = Engine_Api::_()->getApi('settings', 'core')->getSetting('chat.im.privacy', 'friends');
    $return = array();
    
    switch( $conf ) {
      default:
      case 'friends':
        $ids = $this->getUser()->membership()->getMembershipsOfIds();
        $data = $this->getTable()->find($ids);
        foreach( $data as $row ) {
          $return[] = $row->user_id;
        }
        break;
      case 'everyone':
        $data = $this->getTable()->fetchAll();
        foreach( $data as $row ) {
          if( $row->user_id == $this->user_id ) continue;
          $return[] = $row->user_id;
        }
        break;
    }

    $this->_presenceUsers = $return;

    return $return;
  }
  
  protected function _insert()
  {
    $ids = $this->getUsersToBeNotifiedOfPresence();

    if( !empty($ids) ) {

      // Announce presence to all online friends
      $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
      foreach( $ids as $id ) {
        $eventTable->insert(array(
          'user_id' => $id,
          'date' => date('Y-m-d H:i:s'),
          'type' => 'presence',
          'body' => array(
            'user_id' => $this->user_id,
            'state' => '1',
          )
        ));
      }

      // Increment event count for each user
      Engine_Api::_()->getDbtable('users', 'chat')->update(array(
        'event_count' => new Zend_Db_Expr('event_count+1'),
      ), array(
        'user_id IN(\''.join("', '", $ids).'\')'
      ));

    }

    parent::_insert();
  }

  protected function _update()
  {
    // Only do this if state changes
    if( !empty($this->_modifiedFields['state']) ) {

      $ids = $this->getUsersToBeNotifiedOfPresence();

      if( !empty($ids) ) {

        // Announce presence to all online friends
        $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
        foreach( $ids as $id ) {
          $eventTable->insert(array(
            'user_id' => $id,
            'date' => date('Y-m-d H:i:s'),
            'type' => 'presence',
            'body' => array(
              'user_id' => $this->user_id,
              'state' => $this->state,
            )
          ));
        }

        // Increment event count for each user
        Engine_Api::_()->getDbtable('users', 'chat')->update(array(
          'event_count' => new Zend_Db_Expr('event_count+1'),
        ), array(
          'user_id IN(\''.join("', '", $ids).'\')'
        ));
      }
    }
  }

  protected function _delete()
  {
    $ids = $this->getUsersToBeNotifiedOfPresence();

    if( !empty($ids) ) {

      // Announce presence to all online friends
      $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
      foreach( $ids as $id ) {
        $eventTable->insert(array(
          'user_id' => $id,
          'date' => date('Y-m-d H:i:s'),
          'type' => 'presence',
          'body' => array(
            'user_id' => $this->user_id,
            'state' => '0',
          )
        ));
      }

      // Increment event count for each user
      Engine_Api::_()->getDbtable('users', 'chat')->update(array(
        'event_count' => new Zend_Db_Expr('event_count+1'),
      ), array(
        'user_id IN(\''.join("', '", $ids).'\')'
      ));

      // Do rooms now
      foreach( $this->getRoomUsers() as $roomuser ) {
        $roomuser->delete();
      }
      
    }

    parent::_delete();
  }
}