<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: List.php 7418 2010-09-20 00:18:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_List extends Core_Model_Item_Abstract
{
  protected $_parent_is_owner = true;

  protected $_ordered = false;

  protected $_allowDuplicates = false;

  protected $_searchTriggers = false;

  protected $_child_type;



  // Information

  public function isOrdered()
  {
    if( !$this->_ordered ) {
      return false;
    }

    if( !in_array('order', $this->getListItemTable()->info('cols')) ) {
      throw new Core_Model_Item_Exception('Ordering is specified, but no order column detected');
    }

    if( empty($this->order_index) ) {
      throw new Core_Model_Item_Exception('Ordering is specified, but no order_index column detected');
    }
    
    return true;
  }
  
  public function getChildType()
  {
    if( !empty($this->_child_type) ) {
      return $this->_child_type;
    } else if( !empty($this->child_type) ) {
      return $this->child_type;
    } else {
      throw new Core_Model_Item_Exception('No child type defined for list');
    }
  }

  public function getListItemTable()
  {
    return Engine_Api::_()->getItemTable('core_list_item');
  }

  public function getChildTable()
  {
    return Engine_Api::_()->getItemTable($this->getChildType());
  }



  // Simple
  
  public function add(Core_Model_Item_Abstract $child, $params = array())
  {
    if( $child->getType() !== $this->getChildType() )
    {
      throw new Core_Model_Exception('Child and list definition type are not the same');
    }

    if( !$this->_allowDuplicates && $this->has($child) )
    {
      throw new Core_Model_Exception('Duplicates not allowed');
    }
    
    // Create params
    $params = array_merge($params, array(
      'list_id' => $this->getIdentity(),
      'child_id' => $child->getIdentity(),
    ));

    if( $this->isOrdered() ) {
      $params['order'] = ++$this->order_index;
    }

    // Create new item
    $listItem = $this->getListItemTable()->createRow();
    $listItem->setFromArray($params);
    $listItem->save();

    $this->child_count++;
    $this->save();
  }

  public function get(Core_Model_Item_Abstract $child)
  {
    $table = $this->getListItemTable();
    $select = $table->select()
      ->where('list_id = ?', $this->getIdentity())
      ->where('child_id = ?', $child->getIdentity())
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function has(Core_Model_Item_Abstract $child)
  {
    return ( null !== $this->get($child) );
  }

  public function remove(Core_Model_Item_Abstract $child)
  {
    $listItem = $this->get($child);

    if( null !== $listItem )
    {
      $listItem->delete();
      $this->child_count--;
      $this->save();
    }

    return $this;
  }



  // Complex

  public function getAllChildren()
  {
    $childTable = $this->getChildTable();
    return $childTable->fetchAll($this->getChildSelect());
  }

  public function getChildSelect()
  {
    $childTable = $this->getChildTable();
    $childTableName = $childTable->info('name');
    $listItemTable = $this->getListItemTable();
    $listItemTableName = $listItemTable->info('name');

    $col = current($childTable->info('primary'));

    $select = $childTable->select()
      ->from($childTableName)
      ->joinRight($listItemTableName, "`{$listItemTableName}`.`child_id` = `{$childTableName}`.`{$col}`", null)
      ->where("`{$listItemTableName}`.`list_id` = ?", $this->getIdentity())
      ;

    return $select;
  }

  public function getChildPaginator()
  {
    return Zend_Paginator::factory($this->getChildSelect());
  }

  public function getAll()
  {
    $listItemTable = $this->getListItemTable();
    return $listItemTable->fetchAll($this->getSelect());
  }

  public function getSelect()
  {
    return $this->getListItemTable()->select()
      ->where('list_id = ?', $this->getIdentity());
  }
  
  public function getPaginator()
  {
    return Zend_Paginator::factory($this->getSelect());
  }



  // Ordering

  public function getNextHighestOrder()
  {
    $table = $this->getListItemTable();
    $select = new Zend_Db_Select($table->getAdapter());
  }

  public function getOrderedSelect()
  {
    if( !$this->isOrdered() ) {
      throw new Core_Model_Item_Exception('Not ordered and trying to get ordered select');
    }

    return $this->getSelect()
      ->order('order ASC');
  }

  public function getOrderedChilrenSelect()
  {
    if( !$this->isOrdered() ) {
      throw new Core_Model_Item_Exception('Not ordered and trying to get ordered select');
    }

    return $this->getChildSelect()
      ->order('order ASC');
  }




  // Internal hooks

  protected function _delete()
  {
    foreach( $this->getAll() as $listitem ) {
      $listitem->delete();
    }
    parent::_delete();
  }
}