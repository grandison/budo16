<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Allow.php 7481 2010-09-27 08:41:01Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Authorization_Model_DbTable_Allow extends Engine_Db_Table
  implements Authorization_Model_AdapterInterface
{
  public function getAdapterName()
  {
    return 'context';
  }

  public function getAdapterPriority()
  {
    return 50;
  }


  /**
   * Valid relationship types. Ordered by speed of calculation
   *
   * @var array
   */
  protected $_relationships = array(
    'everyone',
    'registered',
    'member',
    'member_requested',
    'owner',
    'parent',
    'owner_member',
    'member_member',
    'network',
    'owner_member_member',
    'owner_network'
  );

  public function isAllowed($resource, $role, $action)
  {
    // Resource must be an instance of Core_Model_Item_Abstract
    if( !($resource instanceof Core_Model_Item_Abstract) )
    {
      // We have nothing to say about generic permissions
      return Authorization_Api_Core::LEVEL_IGNORE;
    }

    // Role must be an instance of Core_Model_Item_Abstract or a string relationship type
    if( !($role instanceof Core_Model_Item_Abstract) && !is_string($role) )
    {
      // Disallow access to unknown role types
      return Authorization_Api_Core::LEVEL_DISALLOW;
    }

    // Owner can do what they want with the resource
    if( ($role instanceof Core_Model_Item_Abstract && method_exists($resource, 'isOwner') && $resource->isOwner($role)) || $role === 'owner' )
    {
      return Authorization_Api_Core::LEVEL_ALLOW;
    }

    // Now go over set permissions
    // @todo allow for custom types
    $rowset = $this->_getAllowed($resource, $role, $action);

    if( is_null($rowset) || !($rowset instanceof Engine_Db_Table_Rowset) )
    {
      // No permissions have been defined for resource, disallow all
      return Authorization_Api_Core::LEVEL_DISALLOW;
    }

    // Index by type
    $perms = array();
    $permsByOrder = array();
    $items = array();
    foreach( $rowset as $row ) {
      if( empty($row->role_id) ) {
        $index = array_search($row->role, $this->_relationships);
        if( $index === false ) { // Invalid type
          continue;
        }
        $perms[$row->role] = $row;
        $permsByOrder[$index] = $row->role;
      } else {
        $items[] = $row;
      }
    }

    // We we're passed a type role, how convenient
    if( is_string($role) ) {
      if( isset($perms[$role]) && is_object($perms[$role]) && $perms[$role]->value == Authorization_Api_Core::LEVEL_ALLOW ) {
        return Authorization_Api_Core::LEVEL_ALLOW;
      } else {
        return Authorization_Api_Core::LEVEL_DISALLOW;
      }
    }

    // Scan available types
    foreach( $permsByOrder as $perm => $type ) {
      $row = $perms[$type];
      $method = 'is_' . $type;
      if( !method_exists($this, $method) ) continue;
      $applies = $this->$method($resource, $role);
      if( $applies && $row->value == Authorization_Api_Core::LEVEL_ALLOW ) {
        return Authorization_Api_Core::LEVEL_ALLOW;
      }
    }

    // Ok, lets check the items then
    foreach( $items as $row ) {
      if( !Engine_Api::_()->hasItemType($row->role) ) {
        continue;
      }

      // Item itself is auth'ed
      if( is_object($role) && $role->getType() == $row->role && $role->getIdentity() == $row->role_id ) {
        return Authorization_Api_Core::LEVEL_ALLOW;
      }

      // Get item class
      $itemClass = Engine_Api::_()->getItemClass($row->role);

      // Member of
      if( method_exists($itemClass, 'membership') ) {
        $item = Engine_Api::_()->getItem($row->role, $row->role_id);
        if( $item && $item->membership()->isMember($role, null, $row->subgroup_id) ) {
          return Authorization_Api_Core::LEVEL_ALLOW;
        }
      }

      // List
      else if( method_exists($itemClass, 'has') ) {
        $item = Engine_Api::_()->getItem($row->role, $row->role_id);
        if( $item && $item->has($role) ) {
          return Authorization_Api_Core::LEVEL_ALLOW;
        }
      }
    }
    
    return Authorization_Api_Core::LEVEL_DISALLOW;
  }

  public function getAllowed($resource, $role, $action)
  {
    // Non-boolean values are not yet implemented
    return $this->isAllowed($resource, $role, $action);
  }

  public function setAllowed($resource, $role, $action, $value = false, $role_id = 0)
  {
    // Can set multiple actions
    if( is_array($action) )
    {
      foreach( $action as $key => $value )
      {
        $this->setAllowed($resource, $role, $key, $value, $role_id);
      }

      return $this;
    }

    // Resource must be an instance of Core_Model_Item_Abstract
    if( !($resource instanceof Core_Model_Item_Abstract) )
    {
      throw new Authorization_Model_Exception('$resource must be an instance of Core_Model_Item_Abstract');
    }

    // Role must be a string, NOT a Core_Model_Item_Abstract
    //if( !is_string($role) )
    //{
    //  throw new Authorization_Model_Exception('$role must be a string relationship type');
    //}

    // Ignore owner (since owner is allowed everything)
    if( $role === 'owner' ) {
      return $this;
    }

    if( is_string($role) ) {
      $role_id = 0;
    } else if( $role instanceof Core_Model_Item_Abstract ) {
      $role_id = $role->getIdentity();
      $role = $role->getType();
    }

    // Try to get an existing row
    $select = $this->select()
      ->where('resource_type = ?', $resource->getType())
      ->where('resource_id = ?', $resource->getIdentity())
      ->where('action = ?', $action)
      ->where('role = ?', $role)
      ->where('role_id = ?', $role_id)
      ->limit(1);

    $row = $this->fetchRow($select);

    $deleteOnDisallow = true;

    // Whoops, create a new row)
    if( null === $row && (!$deleteOnDisallow || $value) )
    {
      $row = $this->createRow();
      $row->resource_type = $resource->getType();
      $row->resource_id = $resource->getIdentity();
      $row->action = $action;
      $row->role = $role;
      $row->role_id = $role_id;
    }

    if( null !== $row ) {
      if( !$deleteOnDisallow || $value ) {
        $row->value = (bool) $value;
        $row->save();
      } else if( $deleteOnDisallow && !$value ) {
        $row->delete();
      }
    }

    return $this;
  }

  protected function _getAllowed($resource, $role, $action)
  {
    // Make sure resource has an id (that it exists)
    $resource_type = $this->_getResourceType($resource);
    $resource_id = $this->_getResourceIdentity($resource);
    if( is_null($resource_id) )
    {
      return null;
    }

    // Get permissions
    $select = $this->select()
      ->where('resource_type = ?', $resource_type)
      ->where('resource_id = ?', $resource_id)
      ->where('action = ?', $action);
      //->where('role_type = ?', $role_guid[0])
      //->where('role_id = ?', $role_guid[1])

    return $this->fetchAll($select);
  }




  // Calculators

  // Tier 1

  public function is_everyone($resource, $role)
  {
    return true;
  }

  public function is_registered($resource, $role)
  {
    if( $role === 'registered' ) {
      return true;
    }
    if( !$role instanceof Core_Model_Item_Abstract ) {
      return false;
    }
    return (bool) $role->getIdentity();
  }

  public function is_member($resource, $role)
  {
    if( $role === 'member' ) {
      return true;
    }
    //if( !$role instanceof Core_Model_Item_Abstract ) {
    if( !$role instanceof User_Model_User ) {
      return false;
    }
    if( !method_exists($resource, 'membership') ) {
      return false;
    }
    return $resource->membership()->isMember($role, true);
  }

  public function is_member_requested($resource, $role)
  {
    if( $role === 'member_requested' ) {
      return true;
    }
    //if( !$role instanceof Core_Model_Item_Abstract ) {
    if( !$role instanceof User_Model_User ) {
      return false;
    }
    if( !method_exists($resource, 'membership') ) {
      return false;
    }
    $info = $resource->membership()->getMemberInfo($role);
    return ( null !== $info && $info->resource_approved && !$info->active );
  }

  public function is_owner($resource, $role)
  {
    if( $role === 'owner' ) {
      return true;
    }
    if( !$role instanceof Core_Model_Item_Abstract ) {
      return false;
    }
    return $role->isSelf($resource->getOwner());
  }

  public function is_parent($resource, $role)
  {
    if( $role === 'parent' ) {
      return true;
    }
    if( !$role instanceof Core_Model_Item_Abstract ) {
      return false;
    }
    return $role->isSelf($resource->getParent());
  }

  // Tier 2

  public function is_owner_member($resource, $role)
  {
    if( $role === 'owner_member' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    $owner = $resource->getOwner();
    if( !method_exists($owner, 'membership') ) {
      return false;
    }
    return $owner->membership()->isMember($role, true);
  }

  public function is_parent_member($resource, $role)
  {
    if( $role === 'parent_member' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    $parent = $resource->getParent();
    if( !method_exists($parent, 'membership') ) {
      return false;
    }
    return $parent->membership()->isMember($role, true);
  }

  public function is_member_member($resource, $role)
  {
    // Note: this implies is_member
    if( $role === 'member_member' || $role == 'member' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    if( !method_exists($resource, 'membership') ) {
      return false;
    }

    if( $this->is_member($resource, $role) ) {
      return true;
    }

    $resourceMembershipTable = $resource->membership()->getReceiver();
    $userMembershipTable = $role->membership()->getReceiver();

    // Build
    $resourceMembershipTableName = $resourceMembershipTable->info('name');
    $userMembershipTableName = $userMembershipTable->info('name');

    $select = new Zend_Db_Select($resourceMembershipTable->getAdapter());
    $select
      ->from($resourceMembershipTableName, 'user_id')
      ->join($userMembershipTableName, "`{$resourceMembershipTableName}`.`user_id`=`{$userMembershipTableName}_2`.resource_id", null)
      ->where("`{$resourceMembershipTableName}`.resource_id = ?", $resource->getIdentity())
      ->where("`{$userMembershipTableName}_2`.user_id = ?", $role->getIdentity())
      ;

    $data = $select->query()->fetch();
    return !empty($data);
  }

  public function is_network($resource, $role)
  {
    if( $role === 'network' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    if( !($resource instanceof User_Model_User) ) {
      return false;
    }
    $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
    $networkMembershipName = $networkMembershipTable->info('name');

    $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
    $select
      ->from($networkMembershipName, 'user_id')
      ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
      ->where("`{$networkMembershipName}`.user_id = ?", $resource->getIdentity())
      ->where("`{$networkMembershipName}_2`.user_id = ?", $role->getIdentity())
      ;

    $data = $select->query()->fetch();
    return !empty($data);
  }


  // Tier 3

  public function is_owner_member_member($resource, $role)
  {
    // Note: this implies is_owner_member
    if( $role === 'owner_member_member' || $role == 'owner_member' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    $owner = $resource->getOwner();
    return $this->is_member_member($owner, $role); // should work
  }

  public function is_parent_member_member($resource, $role)
  {
    // Note: this implies is_owner_member
    if( $role === 'parent_member_member' || $role == 'parent_member' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    $parent = $resource->getParent();
    return $this->is_member_member($parent, $role); // should work
  }

  public function is_owner_network($resource, $role)
  {
    if( $role === 'owner_network' ) {
      return true;
    }
    if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
      return false;
    }
    $owner = $resource->getOwner();
    return $this->is_network($owner, $role); // should work
  }


  
  // Utility
  
  protected function _getResourceType($resource)
  {
    if( is_string($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[0]) )
    {
      return $resource[0];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getType();
    }

    else
    {
      return null;
    }
  }

  /**
   * Returns the identity of a resource from several possible formats:
   * Core_Model_Item_Abstract->getIdentity()
   * integer
   * array(type, identity)
   *
   * @param mixed $resource
   * @return mixed The identity of the resource
   */
  protected function _getResourceIdentity($resource)
  {
    if( is_numeric($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[1]) )
    {
      return $resource[1];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getIdentity();
    }

    else
    {
      return null;
    }
  }
}