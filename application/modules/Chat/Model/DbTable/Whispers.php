<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Whispers.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_DbTable_Whispers extends Engine_Db_Table
{
  protected $_rowClass = 'Chat_Model_Whisper';

  public function getStaleWhispers(User_Model_User $user)
  {
    // To
    $toSelect = $this->select()
      ->where('recipient_id = ?', $user->getIdentity())
      ->where('recipient_deleted = ?', 0);

    // From
    $fromSelect = $this->select()
      ->where('sender_id = ?', $user->getIdentity())
      ->where('sender_deleted = ?', 0);

    // Union
    $select = new Zend_Db_Select($this->getAdapter());
    $select
      ->union(array('('.$toSelect->__toString().')'))
      ->union(array('('.$fromSelect->__toString().')'))
      ->order('whisper_id ASC');

    // Get data
    $stmt = $this->_db->query($select);
    $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

    // Make rowset
    $data  = array(
      'table'    => $this,
      'data'     => $rows,
      'readOnly' => false,
      'rowClass' => $this->getRowClass(),
      'stored'   => true
    );

    $rowsetClass = $this->getRowsetClass();
    if (!class_exists($rowsetClass)) {
        // require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($rowsetClass);
    }
    return new $rowsetClass($data);
  }

  public function closeConversation(User_Model_User $user, $other_user_id)
  {
    $other_user_id = (int) $other_user_id;

    // Close sender
    $this->update(array(
      'sender_deleted' => 1
    ), array(
      'sender_id = ?' => $user->getIdentity(),
      'recipient_id = ?' => $other_user_id
    ));

    // Close recipient
    $this->update(array(
      'recipient_deleted' => 1
    ), array(
      'recipient_id = ?' => $user->getIdentity(),
      'sender_id = ?' => $other_user_id
    ));
  }

  public function gc()
  {
    $this->delete(array(
      'recipient_deleted = ?' => 1,
      'sender_deleted = ?' => 1,
    ));
  }
}