<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Options.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_DbTable_Options extends Fields_Model_DbTable_Abstract
{
  protected $_fieldOptions;

  protected $_fieldOptionsIndex;

  protected $_rowClass = 'Fields_Model_Option';

  public function getOptions()
  {
    if( null === $this->_fieldOptions ) {
      if( ($data = $this->_getCache()) instanceof Zend_Db_Table_Rowset_Abstract ) {
        $this->_fieldOptions = $data;
      } else {
        $this->_fieldOptions = $this->fetchAll($this->select()->order('order'));
        $this->_setCache($this->_fieldOptions);
      }
    }
    return $this->_fieldOptions;
  }

  public function getOptionsAssoc()
  {
    if( null === $this->_fieldOptionsIndex ) {
      $options = $this->getOptions();
      foreach( $options as $option ) {
        $this->_fieldOptionsIndex[$option->option_id] = $option;
      }
    }
    return $this->_fieldOptionsIndex;
  }

  public function getOptionById($id)
  {
    if( null === $this->_fieldOptionsIndex ) {
      $this->getOptionsAssoc();
    }

    if( isset($this->_fieldOptionsIndex[$id]) ) {
      return $this->_fieldOptionsIndex[$id];
    } else {
      return null;
    }
  }

  public function createOption($field, $params)
  {
    // Check if can have deps
    if( !($field instanceof Fields_Model_Meta) || !$field->canHaveDependents() )
    {
      throw new Fields_Model_Exception("Specified field cannot have options");
    }

    // Standardize options
    if( is_string($params) )
    {
      $label = $params;
      $params = array();
      $params['label'] = $label;
    }

    $params['field_id'] = $field->field_id;

    // Save
    $row = $this->getOptions()->createRow();
    $row->setFromArray($params);
    $row->save();

    // Rebuild search for field?
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'search')->checkSearchIndex($field);

    // Update cache
    //$this->_setCache($this->_fieldOptions);
    // Eh, just flush cache
    $this->_flushCache();
    
    return $row;
  }

  public function editOption($option, $params)
  {
    if( is_string($params) ) {
      $option->label = $params;
    } else {
      unset($option['field_id']);
      $option->setFromArray($params);
    }
    $option->save();

    // Update cache
    //$this->_setCache($this->_fieldOptions);
    // Eh, just flush cache
    $this->_flushCache();
    
    return $this;

  }

  public function deleteOption($option)
  {
    // Remove values from values and search table where it's this option
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'search')->flushOptionSearch($option);
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'values')->flushOptionValues($option);

    // Remove this option
    $option->delete();

    // Flush cache
    $this->_flushCache();
    
    return $this;
  }

  public function deleteFieldOptions($field)
  {
    // Remove options
    foreach( $this->getOptions() as $option ) {
      if( $option->field_id == $field->field_id ) {
        $this->deleteOption($option);
      }
    }

    // Flush local cache
    $this->_fieldOptions = null;
    $this->_fieldOptionsIndex = null;

    return $this;
  }
}