<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Auth.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Auth extends Engine_Db_Table
{
  public function createKey(User_Model_User $user, $type = null, $expires = 0)
  {
    $staticSalt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_secret', php_uname());
    $key = sha1($staticSalt . $user->salt . $user->getIdentity() . uniqid('', true));

    $row = $this->createRow();
    $row->id = $key;
    $row->user_id = $user->getIdentity();
    $row->expires = (int) $expires;
    $row->type = $type;
    $row->save();

    return $row;
  }

  public function checkKey(User_Model_User $user, $key, $type = null)
  {
    // @todo
    return $this;
  }

  public function getKey(User_Model_User $user, $type = null, $expires = 0)
  {
    $select = $this->select()
      ->where('user_id = ?', $user->getIdentity())
      ;

    if( null !== $type ) {
      $select->where('type = ?', $type);
    }

    if( !$expires ) {
      $select->where('expires = ?', 0);
    } else {
      $select->where('expires > ?', time());
    }

    $row = $this->fetchRow($select);

    if( null === $row ) {
      return $this->createKey($user, $type, $expires);
    } else {
      return $row;
    }
  }

  public function cleanup()
  {
    $this->delete(array(
      'expires < ?' => time(),
      'expires > ?' => 0,
    ));
    
    return $this;
  }
}