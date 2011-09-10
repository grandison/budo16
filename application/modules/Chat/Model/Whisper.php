<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Whisper.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_Whisper extends Engine_Db_Table_Row
{
  public function toRemoteArray()
  {
    $return = array(
      'type' => 'chat',
      'whisper_id' => $this->whisper_id,
      'sender_id' => $this->sender_id,
      'recipient_id' => $this->recipient_id,
      'body' => $this->body,
      'date' => $this->date
    );

    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      if( $viewer->getIdentity() == $this->sender_id ) {
        $return['user_id'] = $this->recipient_id;
      } else if( $viewer->getIdentity() == $this->recipient_id ) {
        $return['user_id'] = $this->sender_id;
      }
    }

    return $return;
  }

  protected function _postInsert()
  {
    // Announce message
    $eventTable = Engine_Api::_()->getDbtable('events', 'chat');
    $eventTable->insert(array(
      'user_id' => $this->recipient_id,
      'date' => date('Y-m-d H:i:s'),
      'type' => 'chat',
      'body' => array(
        'user_id' => $this->sender_id,
        'whisper_id' => $this->whisper_id,
      )
    ));

    // Announce to ourselves too ... -_-
    $eventTable->insert(array(
      'user_id' => $this->sender_id,
      'date' => date('Y-m-d H:i:s'),
      'type' => 'chat',
      'body' => array(
        'user_id' => $this->sender_id,
        'whisper_id' => $this->whisper_id,
      )
    ));
    

    // Increment event count for each user
    Engine_Api::_()->getDbtable('users', 'chat')->update(array(
      'event_count' => new Zend_Db_Expr('event_count+1'),
    ), array(
      'user_id = ?' => $this->recipient_id
    ));
  }
}