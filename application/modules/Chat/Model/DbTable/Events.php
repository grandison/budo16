<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Events.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_DbTable_Events extends Engine_Db_Table
{
  protected $_rowClass = 'Chat_Model_Event';

  protected $_serializedColumns = array('body');

  public function getEvents(User_Model_User $user, $time = null)
  {
    $select = $this->select()
      ->where('user_id = ?', $user->user_id)
      //->where('date > ?', date("Y-m-d h:i:s", $ts))
      ->order('date ASC');

    if( null !== $time ) {
      $select->where('date > FROM_UNIXTIME(?)', $time);
    }

    return $this->fetchAll($select);
  }

  public function clearEvents(Chat_Model_User $user)
  {
    $this->delete(array(
      'user_id = ?' => $user->user_id
    ));

    $user->event_count = 0;
    $user->save();
  }

  public function gc()
  {
    $this->delete(array(
      'date < FROM_UNIXTIME(?)' => time() - 120
    ));
  }
}