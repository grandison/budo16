<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RoomUser.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_RoomUser extends Engine_Db_Table_Row
{
  protected $_room;
  
  public function setRoom(Chat_Model_Room $room = null)
  {
    $this->_room = $room;
    return $this;
  }
  
  public function getRoom()
  {
    if( null === $this->_room ) {
       $this->_room = Engine_Api::_()->getDbtable('rooms', 'chat')->find($this->room_id)->current();
    }

    return $this->_room;
  }
  
  protected function _postInsert()
  {
    // Increment room user count
    $room = $this->getRoom();
    //$room->user_count++;
    $room->user_count = new Zend_Db_Expr('user_count + 1');
    $room->save();

    // Announce prescence
    $ids = $this->getRoom()->getUserIds();

    // Remove self
    if( false !== ($index = array_search($this->user_id, $ids)) ) {
      //unset($ids[$index]);
    }
    
    if( !empty($ids) ) {

      $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
      foreach( $ids as $id ) {
        $eventTable->createRow()->setFromArray(array(
          'user_id' => $id,
          'date' => date('Y-m-d H:i:s'),
          'type' => 'grouppresence',
          'body' => array(
            'room_id' => $this->room_id,
            'user_id' => $this->user_id,
            'state' => '1',
          )
        ))->save();
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
      
      // Announce presence
      $ids = $this->getRoom()->getUserIds();

      // Remove self
      if( false !== ($index = array_search($this->user_id, $ids)) ) {
        //unset($ids[$index]);
      }

      if( !empty($ids) ) {

        $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
        foreach( $ids as $id ) {
          $eventTable->createRow()->setFromArray(array(
            'user_id' => $id,
            'date' => date('Y-m-d H:i:s'),
            'type' => 'grouppresence',
            'body' => array(
              'room_id' => $this->room_id,
              'user_id' => $this->user_id,
              'state' => $this->state,
            )
          ))->save();
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
    // Decrement room user count
    $room = $this->getRoom();
    //$room->user_count--;
    if( null !== $room ) {
      $room->user_count = new Zend_Db_Expr('user_count - 1');
      $room->save();

      // Announce loss of prescence
      $ids = $this->getRoom()->getUserIds();

      // Remove self
      if( false !== ($index = array_search($this->user_id, $ids)) ) {
        unset($ids[$index]);
      }

      if( !empty($ids) ) {

        $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
        foreach( $ids as $id ) {
          $eventTable->createRow()->setFromArray(array(
            'user_id' => $id,
            'date' => date('Y-m-d H:i:s'),
            'type' => 'grouppresence',
            'body' => array(
              'room_id' => $this->room_id,
              'user_id' => $this->user_id,
              'state' => '0',
            )
          ))->save();
        }

        // Increment event count for each user
        Engine_Api::_()->getDbtable('users', 'chat')->update(array(
          'event_count' => new Zend_Db_Expr('event_count+1'),
        ), array(
          'user_id IN(\''.join("', '", $ids).'\')'
        ));

      }
    }

    parent::_delete();
  }
}