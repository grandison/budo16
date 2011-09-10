<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RoomUsers.php 7634 2010-10-13 23:28:00Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_DbTable_RoomUsers extends Engine_Db_Table
{
  protected $_rowClass = 'Chat_Model_RoomUser';

  public function check(User_Model_User $user, $rooms = array())
  {
    if( !is_array($rooms) ) return;
    foreach( $rooms as $index => $room_id ) {
      if( !is_numeric($room_id) ) {
        unset($rooms[$index]);
      }
    }
    if( empty($rooms) ) return;

    $this->update(array(
      'date' => date('Y-m-d H:i:s')
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'room_id IN(?)' => $rooms
    ));
  }

  public function gc()
  {
    $select = $this->select()
      //->where('date < ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 5 SECOND)'));
      ->where('date < ?', date('Y-m-d H:i:s', time()-15));

    foreach( $this->fetchAll($select) as $roomUser ) {
      $roomUser->delete();
    }
  }
}