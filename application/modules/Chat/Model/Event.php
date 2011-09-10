<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Event.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_Event extends Engine_Db_Table_Row
{
  public function toRemoteArray()
  {
    $return = array();
    switch( $this->type ) {
      
      case 'presence':
        $user = Engine_Api::_()->getDbtable('users', 'chat')->find($this->body['user_id'])->current();
        if( is_object($user) ) {
          $return = $user->toRemoteArray();
        } else {
          $return = array(
            'identity' => $this->body['user_id']
          );
        }
        $return['type'] = 'presence';
        $return['state'] = $this->body['state'];
        break;

      case 'chat':
        $whisper = Engine_Api::_()->getDbtable('whispers', 'chat')->find($this->body['whisper_id'])->current();
        $return = $whisper->toRemoteArray();
        break;

      case 'grouppresence':
        $user = Engine_Api::_()->getDbtable('users', 'chat')->find($this->body['user_id'])->current();
        if( is_object($user) ) {
          $return = $user->toRemoteArray();
        } else {
          $return = array(
            'type' => 'grouppresence',
            'identity' => $this->body['user_id']
          );
        }
        $return['type'] = 'grouppresence';
        $return['room_id'] = $this->body['room_id'];
        $return['state'] = $this->body['state'];
        // *tear*
        $viewer = Engine_Api::_()->user()->getViewer();
        $return['self'] = ( $viewer->getIdentity() == $return['identity'] );
        break;

      case 'groupchat':
        $message = Engine_Api::_()->getDbtable('messages', 'chat')->find($this->body['message_id'])->current();
        $return = $message->toRemoteArray();
        break;

      case 'reconfigure':
        $return = $this->toArray();
        $return = array_merge($return, $return['body']);
        unset($return['body']);
        break;
    }

    return $return;
  }
}