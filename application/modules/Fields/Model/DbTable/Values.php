<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Values.php 8153 2011-01-06 17:39:23Z char $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_DbTable_Values extends Fields_Model_DbTable_Abstract
{
  protected $_fieldValues = array();

  protected $_fieldValuesIndex = array();

  protected $_rowClass = 'Fields_Model_Value';



  // Retv
  
  public function getValues($item)
  {
    $id = $this->_getIdentity($item);
    
    if( !array_key_exists($id, $this->_fieldValues) ) {
      $this->_fieldValues[$id] = $this->fetchAll($this->select()->where('item_id = ?', $id));
    }

    return $this->_fieldValues[$id];
  }

  public function getValuesAssoc($item)
  {
    $id = $this->_getIdentity($item);
    
    if( !array_key_exists($id, $this->_fieldValues) ) {
      $this->getValues($id);
    }

    if( !array_key_exists($id, $this->_fieldValuesIndex) ) {
      $rv = $this->getValues($id);
      $this->_fieldValuesIndex[$id] = array();
      foreach( $rv as $val ) {
        $this->_fieldValuesIndex[$id][$val->item_id] = $val;
      }
    }

    return $this->_fieldValuesIndex[$id];
  }

  public function getValuesById($item, $field_id)
  {
    $id = $this->_getIdentity($item);

    if( !@array_key_exists($id, $this->_fieldValuesIndex) ) {
      $this->getValuesAssoc($id);
    }

    if( !isset($this->_fieldValuesIndex[$id][$field_id]) ) {
      return null;
    }

    return $this->_fieldValuesIndex[$id][$field_id];
  }

  public function clearValues()
  {
    $this->_fieldValues = array();
    $this->_fieldValuesIndex = array();
    return $this;
  }



  // Op



  // Tert

  public function removeItemValues($item)
  {
    $id = $this->_getIdentity($item);

    unset($this->_fieldValues[$id]);
    unset($this->_fieldValuesIndex[$id]);
    $this->delete(array(
      'item_id = ?' => $id,
    ));

    return $this;
  }

  public function deleteFieldValues($field)
  {
    $this->delete(array(
      'field_id = ?' => $field->field_id,
    ));

    // Wish I didn't have to do this
    $this->_fieldValues = array();
    $this->_fieldValuesIndex = array();

    return $this;
  }

  public function flushOptionValues($option)
  {
    $this->delete(array(
      'field_id = ?' => $option->field_id,
      'value = ?' => $option->option_id,
    ));

    // Wish I didn't have to do this
    $this->_fieldValues = array();
    $this->_fieldValuesIndex = array();

    return $this;
  }



  // Utility
  
  protected function _getIdentity($item)
  {
    $id = null;
    if( $item instanceof Core_Model_Item_Abstract ) {
      if( $item->getType() != $this->_fieldType ) {
        throw new Fields_Model_Exception('field type does not match item type');
      }
      $id = $item->getIdentity();
    } else if( is_numeric($item) ) {
      $id = $item;
    } else {
      throw new Fields_Model_Exception('invalid item');
    }
    return $id;
  }
}