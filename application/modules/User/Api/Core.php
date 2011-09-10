<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7737 2010-11-04 20:31:54Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Api_Core extends Core_Api_Abstract
{
  /**
   * @var array User objects by id
   */
  protected $_users = array();

  /**
   * @var arraay User ids by email or id or username
   */
  protected $_indexes = array();

  /**
   * @var User_Model_User Contains the current viewer instance
   */
  protected $_viewer;

  /**
   * @var Zend_Auth Authentication object
   */
  protected $_auth;

  /**
   * @var Zend_Auth_Adapter_Interface Authentication adapter object
   */
  protected $_authAdapter;



  // Users

  /**
   * Gets an instance of a user
   *
   * @param mixed $identity An id, username, or email
   * @return User_Model_User
   */
  public function getUser($identity)
  {
    // Identity is already a user!
    if( $identity instanceof User_Model_User )
    {
      return $identity;
    }

    // Lookup in index
    $user = $this->_lookupUser($identity);
    if( $user instanceof User_Model_User )
    {
      return $user;
    }
    
    // Create new instance
    $user = $this->_getUser($identity);
    if( null === $user ) {
      $user = new User_Model_User(array());
    } else {
      $this->_indexUser($user);
    }

    return $user;
  }
  
  /**
   * Gets an instance of multiple users
   *
   * @param array $ids
   * @return array An array of Core_Model_Item_Abstract
   */
  public function getUserMulti(array $ids)
  {
    // Remove any non-numeric values and already retv rows
    $getIds = array();
    foreach( $ids as $index => $value )
    {
      if( !is_numeric($value) )
      {
        unset($ids[$index]);
      }
      else if( !isset($this->_users[$value]) )
      {
        $getIds[] = $value;
      }
    }

    // Now get any remaining rows, if necessary
    if( !empty($getIds) )
    {
      foreach( Engine_Api::_()->getItemTable('user')->find($getIds) as $row )
      {
        $user = $this->_getUser($row->getIdentity());
        $this->_indexUser($user);
      }
    }

    // Now build the return data
    $users = array();
    foreach( $ids as $id )
    {
      if( isset($this->_users[$id]) )
      {
        $users[] = $this->_users[$id];
      }
    }

    return $users;
  }



  // Viewer

  /**
   * Gets the current viewer instance using the authentication storage
   *
   * @return User_Model_User
   */
  public function getViewer()
  {
    if( null === $this->_viewer ){
      $identity = $this->getAuth()->getIdentity();
      $this->_viewer = $this->_getUser($identity);
    }

    return $this->_viewer;
  }

  public function setViewer(User_Model_User $viewer = null)
  {
    $this->_viewer = $viewer;
    return $this;
  }



  // Authentication

  /**
   * Authenticate user
   *
   * @param string $identity Email
   * @param string $credential Password
   * @return Zend_Auth_Result
   */
  public function authenticate($identity, $credential)
  {
    // Translate email
    $userTable = Engine_Api::_()->getItemTable('user');
    $userIdentity = $userTable->select()
      ->from($userTable, 'user_id')
      ->where('`email` = ?', $identity)
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    $authAdapter = $this->getAuthAdapter()
      ->setIdentity($userIdentity)
      ->setCredential($credential);

    return $this->getAuth()->authenticate($authAdapter);
  }

  /**
   * Get the authentication object
   *
   * @return Zend_Auth
   */
  public function getAuth()
  {
    if( is_null($this->_auth) ) {
      $this->_auth = Zend_Auth::getInstance();
      if( _ENGINE_NO_AUTH && !$this->_auth->getIdentity() ) {
        $this->_auth->getStorage()->write(1);
      }
    }
    return $this->_auth;
  }

  /**
   * Set the authentication object
   *
   * @param Zend_Auth $auth
   * @return User_Model_Api
   */
  public function setAuth(Zend_Auth $auth)
  {
    $this->_auth = $auth;
    return $this;
  }

  /**
   * Get the authentication adapter
   *
   * @return Zend_Auth_Adapter_Interface
   */
  public function getAuthAdapter()
  {
    if( is_null($this->_authAdapter) )
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $tablePrefix = Engine_Db_Table::getTablePrefix();
      $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');

      $this->_authAdapter = new Zend_Auth_Adapter_DbTable(
        $db,
        Engine_Api::_()->getItemTable('user')->info('name'),
        'user_id',
        'password',
        "MD5(CONCAT('".$salt."', ?, salt))"
      );
    }
    return $this->_authAdapter;
  }

  /**
   * Set the authentication adapter object
   *
   * @param Zend_Auth_Adapter_Interface $authAdapter
   * @return Zend_Auth
   */
  public function setAuthAdapter(Zend_Auth_Adapter_Interface $authAdapter)
  {
    $this->_authAdapter = $authAdapter;
    return $this;
  }



  /* Utility */

  /**
   * Indexes a user object internally by id, username, email
   *
   * @param User_Model_User $user A user object
   * @return void
   */
  protected function _indexUser(User_Model_User $user)
  {
    // Ignore if not an actual user or user is already set
    if( !empty($user->user_id) && !isset($this->_users[$user->user_id]) )
    {
      $this->_indexes[$user->user_id] = $user->user_id;

      if ( !empty($user->email)){
        $this->_indexes[$user->email] = $user->user_id;
      }

      if ( !empty($user->username)){
        $this->_indexes[$user->username] = $user->user_id;
      }
      
      $this->_users[$user->user_id] = $user;
    }
  }

  /**
   * Looks up a user by id, username, email
   *
    * @param mixed $identity
   * @return integer|false
   */
  protected function _lookupUser($identity)
  {
    $index = null;
    if( is_scalar($identity) && isset($this->_indexes[$identity]) )
    {
      $index = $identity;
    }

    else if( $identity instanceof Zend_Db_Table_Row_Abstract && isset($identity->user_id) )
    {
      $index = $identity->user_id;
    }

    else if( is_array($identity) && is_string($identity[0]) && is_numeric($identity[1]) )
    {
      $index = $identity[1];
    }

    if( isset($this->_indexes[$index]) && isset($this->_users[$this->_indexes[$index]]) )
    {
      return $this->_users[$this->_indexes[$index]];
    }

    return null;
  }

  protected function _getUser($identity)
  {
    if( !$identity ) {
      $user = new User_Model_User(array(
        'table' => Engine_Api::_()->getItemTable('user'),
      ));
    } else if( is_numeric($identity) ) {
      $user = Engine_Api::_()->getItemTable('user')->find($identity)->current();
    } else if( is_string($identity) && strpos($identity, '@') !== false ) {
      $user = Engine_Api::_()->getItemTable('user')->fetchRow(array(
        'email = ?' => $identity,
      ));
    } else /* if( is_string($identity) ) */ {
      $user = Engine_Api::_()->getItemTable('user')->fetchRow(array(
        'username = ?' => $identity,
      ));
    }

    // Empty user?
    if( null === $user ) {
      return new User_Model_User(array());
    }
    
    return $user;
  }

  public function randomPass($len)
  {
  $code = NULL;
        $lchar = '';
        $pass = '';
  for( $i=0; $i<$len; $i++ )
  {
    $char = chr(rand(48,122));
    while( !ereg("[a-zA-Z0-9]", $char) )
    {
      if( $char == $lchar ) continue;
      $char = chr(rand(48,90));
    }
    $pass .= $char;
    $lchar = $char;
  }
  return $pass;
  }


  public function getSuperAdmins(){
    $table = $this->api()->getDbtable('users');
    $select = $table->select()
      ->where('level_id = ?', 1);

    $superadmins = $table->fetchAll($select);
    return $superadmins;
  }

}