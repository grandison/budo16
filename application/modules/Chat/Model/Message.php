<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Message.php 7854 2010-11-26 19:57:51Z shaun $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_Message extends Engine_Db_Table_Row
{
  protected $_room;

  public function toRemoteArray()
  {
    $return = array(
      'type' => 'groupchat',
      'message_id' => $this->message_id,
      'user_id' => $this->user_id,
      'room_id' => $this->room_id,
      'body' => $this->body,
      'date' => $this->date,
      'system' => (int)$this->system
    );

    return $return;
  }

  public function setRoom(Chat_Model_Room $room)
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
    $ids = $this->getRoom()->getUserIds();

    // Remove self
    if( false !== ($index = array_search($this->user_id, $ids)) ) {
      //unset($ids[$index]);
    }

    if( !empty($ids) ) {

      // Announce message
      $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
      foreach( $ids as $id ) {
        $eventTable->insert(array(
          'user_id' => $id,
          'date' => date('Y-m-d H:i:s'),
          'type' => 'groupchat',
          'body' => array(
            'room_id' => $this->room_id,
            'user_id' => $this->user_id,
            'message_id' => $this->message_id,
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