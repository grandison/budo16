<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Users.php 7634 2010-10-13 23:28:00Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Model_DbTable_Users extends Engine_Db_Table
{
  protected $_rowClass = 'Chat_Model_User';

  public function check(User_Model_User $user)
  {
    $count = $this->update(array(
      'date' => date('Y-m-d H:i:s')
    ), array(
      'user_id = ?' => $user->user_id,
    ));
    
    if( $count < 1 ) {
      if( $this->getAdapter() instanceof Zend_Db_Adapter_Mysqli ||
          $this->getAdapter() instanceof Engine_Db_Adapter_Mysql ||
          $this->getAdapter() instanceof Zend_Db_Adapter_Pdo_Mysql ) {
        $sql = 'INSERT IGNORE INTO `'.$this->info('name').'` (`user_id`, `date`) VALUES (?, ?)';
        $sql = $this->getAdapter()->quoteInto($sql, $user->user_id, null, 1);
        $sql = $this->getAdapter()->quoteInto($sql, date('Y-m-d H:i:s'), null, 1);
        $this->getAdapter()->query($sql);
        $row = $this->get($user);
      } else {
        $row = $this->createRow();
        $row->setUser($user);
        $row->setFromArray(array(
          'user_id' => $user->user_id,
          'date' => date('Y-m-d H:i:s'),
          //'state' => 1
        ));
        $row->save();
      }
    } else {
      $row = $this->get($user);
    }
    
    return $row;
  }

  public function has(User_Model_User $user)
  {
    return ( null !== $this->get($user) );
  }

  public function get(User_Model_User $user)
  {
    return $this->find($user->getIdentity())->current();
  }

  public function gc()
  {
    $select = $this->select()
      //->where('date < ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 5 SECOND)'));
      ->where('date < ?', date('Y-m-d H:i:s', time() - 30));

    foreach( $this->fetchAll($select) as $user ) {
      $user->delete();
    }
  }
}