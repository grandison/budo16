<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Membership.php 8596 2011-03-12 02:13:15Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Model_DbTable_Membership extends Engine_Db_Table
{
  protected $_type;

  protected $_rows = array();


  /**
   * @var string The module name of this model (say that 12 times fast)
   */
  protected $_moduleName;

  /**
   * Get the module this model belongs to
   *
   * @return string The module name of this model
   */
  public function getModuleName()
  {
    if( empty($this->_moduleName) )
    {
      $class = get_class($this);
      if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
        $prefix = $matches[1];
      } else {
        $prefix = $class;
      }
      $this->_moduleName = $prefix;
    }
    return $this->_moduleName;
  }

  // Tables

  /**
   * Gets the table associated with this membership type.
   *
   * @return Engine_Db_Table
   */
  public function getTable()
  {
    return $this;
  }



  // General

  /**
   * Gets the row associated with the specified resource/member pair in the db
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @return Engine_Db_Table_Row|false
   */
  public function getRow(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $id = $resource->getIdentity().'_'.$user->getIdentity();
    if( !isset($this->_rows[$id]) )
    {
      $table = $this->getTable();
      $select = $table->select()
        ->where('resource_id = ?', $resource->getIdentity())
        ->where('user_id = ?', $user->getIdentity());

      $select = $select->limit(1);
      $row = $table->fetchRow($select);
      //if( $row === null )
      //{
      //  $this->_rows[$id] = false;
      //}
      //else
      //{
        $this->_rows[$id] = $row;
      //}
    }
    return $this->_rows[$id];
  }

  public function clearRows()
  {
    $this->_rows = array();
    return $this;
  }


  // Configuration

  /**
   * Does membership require approval of the resource?
   *
   * @param Core_Model_Item_Abstract $resource
   * @return bool
   */
  public function isResourceApprovalRequired(Core_Model_Item_Abstract $resource)
  {
    return true;
  }

  /**
   * Does membership require approval of the user?
   *
   * @param Core_Model_Item_Abstract $resource
   * @return bool
   */
  public function isUserApprovalRequired(Core_Model_Item_Abstract $resource)
  {
    return true;
  }



  // Member management

  /**
   * Add $user as member to $resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @return Core_Model_DbTable_Membership
   */
  public function addMember(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $this->_isSupportedType($resource);
    $row = $this->getRow($resource, $user);

    if( null !== $row )
    {
      throw new Core_Model_Exception('That user is already a member');
    }

    $id = $resource->getIdentity().'_'.$user->getIdentity();
    $row = $this->getTable()->createRow();
    $row->setFromArray(array(
      'resource_id' => $resource->getIdentity(),
      'user_id' => $user->getIdentity(),
      'resource_approved' => !$this->isResourceApprovalRequired($resource),
      'user_approved' => 0,
      'active' => 0
    ));
    $row->save();
    $this->_rows[$id] = $row;
    $this->_checkActive($resource, $user);

    return $this;
  }

  /**
   * Remove $user as member of $resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @return Core_Model_DbTable_Membership
   */
  public function removeMember(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $this->_isSupportedType($resource);
    $row = $this->getRow($resource, $user);

    if( null === $row )
    {
      throw new Core_Model_Exception("Membership does not exist");
    }

    if( isset($resource->member_count) && $row->active )
    {
      $resource->member_count--;
      $resource->save();
    }

    $row->delete();

    return $this;
  }

  public function removeMembers(Core_Model_Item_Abstract $resource, $ids)
  {
    // @todo
  }

  public function removeAllMembers(Core_Model_Item_Abstract $resource)
  {
    $this->getTable()->delete(array(
      'resource_id = ?' => $resource->getIdentity()
    ));
    if( isset($resource->member_count) ) {
      $resource->member_count = 0;
      $resource->save();
    }
    return $this;
  }

  /**
   * Set membership as being approved by the resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @return Core_Model_DbTable_Membership
   */
  public function setResourceApproved(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $this->_isSupportedType($resource);
    $row = $this->getRow($resource, $user);

    if( null === $row )
    {
      throw new Core_Model_Exception("Membership does not exist");
    }

    if( !$row->resource_approved )
    {
      $row->resource_approved = true;
      if( $row->resource_approved && $row->user_approved )
      {
        $row->active = true;
        if( isset($resource->member_count) )
        {
          $resource->member_count++;
          $resource->save();
        }
      }
      $this->_checkActive($resource, $user);
      $row->save();
    }

    return $this;
  }

  /**
   * Set membership as being approved by the user
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @return Core_Model_DbTable_Membership
   */
  public function setUserApproved(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $this->_isSupportedType($resource);
    $row = $this->getRow($resource, $user);

    if( null === $row )
    {
      throw new Core_Model_Exception("Membership does not exist");
    }

    if( !$row->user_approved )
    {
      $row->user_approved = true;
      if( $row->resource_approved && $row->user_approved )
      {
        $row->active = true;
        if( isset($resource->member_count) )
        {
          $resource->member_count++;
          $resource->save();
        }
      } 
      $this->_checkActive($resource, $user);
      $row->save();
    }

    return $this;
  }



  // Member info

  /**
   * Checks if specified user is a member of resource. Set $active to true/false
   * to check for approved status, or null for either.
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   * @param bool|null $active
   * @return bool
   */
  public function isMember(Core_Model_Item_Abstract $resource, User_Model_User $user, $active = null)
  {
    $this->_isSupportedType($resource);
    $row = $this->getRow($resource, $user);
    if ($row === null)
    {
      return false;
    }

    if( null === $active )
    {
      return true;
    }

    return ( $active == $row->active );
  }

  public function getMemberInfo(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    return $this->getRow($resource, $user);
  }

  /**
   * Gets the number of members a resource has
   *
   * @param Core_Model_Item_Abstract $resource
   * @param bool $active
   * @return int
   */
  public function getMemberCount(Core_Model_Item_Abstract $resource, $active = true, $other_conditions = array())
  {
    if( isset($resource->member_count) && $active && empty($other_conditions))
    {
      return $resource->member_count;
    }
    else
    {
      $table = $this->getTable();
      $select = new Zend_Db_Select($table->getAdapter());
      $select->from($table->info('name'), new Zend_Db_Expr('COUNT(user_id) as member_count'))
        ->where('resource_id = ?', $resource->getIdentity());

      if( null != $active )
      {
        $select->where('active = ?', (bool) $active);
      }
      foreach ($other_conditions as $condition_name=>$condition_value)
  {
    $select = $select->where($condition_name . ' = ?', $condition_value);
  }
      $row = $table->getAdapter()->fetchRow($select);
      return $row['member_count'];
    }
  }

  /**
   * Gets members that belong to resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param bool|null $active
   * @return Engine_Db_Table_Rowset
   */
  public function getMembers(Core_Model_Item_Abstract $resource, $active = true)
  {
    $ids = array();
    foreach( $this->getMembersInfo($resource, $active) as $row )
    {
      $ids[] = $row->user_id;
    }
    return Engine_Api::_()->getItemTable('user')->find($ids);
  }

  /**
   * Gets membership info for members that belong to resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param bool|null $active
   * @return Engine_Db_Table_Rowset
   */
  public function getMembersInfo(Core_Model_Item_Abstract $resource, $active = true)
  {
    $table = $this->getTable();
    $select = $table->select()->where('resource_id = ?', $resource->getIdentity());

    if( $active !== null )
    {
      $select->where('active = ?', (bool) $active);
    }

    return $table->fetchAll($select);
  }

  public function getMembersSelect(Core_Model_Item_Abstract $resource, $active = true)
  {
    $table = $this->getTable();
    $select = $table->select()
      ->where('resource_id = ?', $resource->getIdentity())
      ;

    if( $active !== null )
    {
      $select->where('active = ?', (bool) $active);
    }

    return $select;
  }

  public function getMembersOfSelect(Core_Model_Item_Abstract $resource, $active = true)
  {
    $table = $this->getTable();
    $select = $table->select()
      ->where('user_id = ?', $resource->getIdentity())
      ;

    if( $active !== null )
    {
      $select->where('active = ?', (bool) $active);
    }

    return $select;
  }

  public function getMembersObjectSelect(Core_Model_Item_Abstract $resource, $active = true)
  {
    $table = Engine_Api::_()->getDbtable('users', 'user');
    $subtable = $this->getTable();
    $tableName = $table->info('name');
    $subtableName = $subtable->info('name');

    $select = $table->select()
      ->from($tableName)
      ->joinRight($subtableName, '`'.$subtableName.'`.`user_id` = `'.$tableName.'`.`user_id`', null)
      ->where('`'.$subtableName.'`.`resource_id` = ?', $resource->getIdentity())
      ;

    if( $active !== null )
    {
      $select->where('`'.$subtableName.'`.`active` = ?', (bool) $active);
    }

    return $select;
  }

  /**
   * Gets resources that $user is a member of in the current type.
   *
   * @param User_Model_User $user
   * @param bool|null $active
   * @return Engine_Db_Table_Rowset
   */
  public function getMembershipsOf(User_Model_User $user, $active = true)
  {
    $ids = $this->getMembershipsOfIds($user, $active);
    return Engine_Api::_()->getItemTable($this->_type)->find($ids);
  }

  public function getMembershipsOfIds(User_Model_User $user, $active = true)
  {
    $ids = array();
    $rows = $this->getMembershipsOfInfo($user, $active);
    foreach( $rows as $row )
    {
      $ids[] = $row->resource_id;
    }
    return $ids;
  }

  /**
   * Gets resource membership info that $user is a member of in the current type.
   *
   * @param User_Model_User $user
   * @param bool|null $active
   * @return Engine_Db_Table_Rowset
   */
  public function getMembershipsOfInfo(User_Model_User $user, $active = true)
  {
    $table = $this->getTable();
    $select = $table->select()->where('user_id = ?', $user->getIdentity());

    if( $active !== null )
    {
      $select->where('active = ?', (bool) $active);
    }
    return $table->fetchAll($select);
  }

  public function getMembershipsOfSelect(User_Model_User $user, $active = true)
  {
    $itemTable = Engine_Api::_()->getItemTable($this->_type);
    $table = $this->getTable();

    $itName = $itemTable->info('name');
    $mtName = $table->info('name');
    $col = current($itemTable->info('primary'));

    $select = $itemTable->select()
      ->from($itName)
      ->joinRight($mtName, "`{$mtName}`.`resource_id` = `{$itName}`.`{$col}`", null)
      ->where("`{$mtName}`.`user_id` = ?", $user->getIdentity())
      ;

    if( $active !== null )
    {
      $select->where("`{$mtName}`.`active` = ?", (bool) $active);
    }

    return $select;
  }


  
  // Utility

  /**
   * Used to check and update active status after addMember, set*Approved
   *
   * @param Core_Model_Item_Abstract $resource
   * @param User_Model_User $user
   */
  protected function _checkActive(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    $row = $this->getRow($resource, $user);

    if( null === $row )
    {
      throw new Core_Model_Exception("Membership does not exist");
    }

    if( $row->resource_approved && $row->user_approved && !$row->active )
    {
      $row->active = true;
      $row->save();
      if( isset($resource->member_count) ) {
        $resource->member_count++;
        $resource->save();
      }
    }
  }

  /**
   * Checks if resource is of the proper type.
   *
   * @param Core_Model_Item_Abstract $resource
   * @throws Core_Model_Exception
   */
  protected function _isSupportedType(Core_Model_Item_Abstract $resource)
  {
    if( $resource->getType() !== $this->_type )
    {
      throw new Core_Model_Exception(sprintf('Type "%s" is not supported', $resource->getType()));
    }
  }
}