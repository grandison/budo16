<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Meta.php 8726 2011-03-28 06:07:37Z char $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_DbTable_Meta extends Fields_Model_DbTable_Abstract
{
  protected $_fieldMeta;

  protected $_fieldMetaIndex;

  protected $_rowClass = 'Fields_Model_Meta';

  protected $_serializedColumns = array(
    'validators',
    'filters',
    'multiOptions',
    'config'
  );
  
  public function getMeta()
  {
    if( null === $this->_fieldMeta ) {
      if( ($data = $this->_getCache()) instanceof Zend_Db_Table_Rowset_Abstract ) {
        $this->_fieldMeta = $data;
      } else {
        $this->_fieldMeta = $this->fetchAll($this->select()->order('order'));
        $this->_setCache($this->_fieldMeta);
      }
    }
    return $this->_fieldMeta;
  }

  public function getMetaAssoc()
  {
    if( null === $this->_fieldMetaIndex ) {
      $meta = $this->getMeta();
      $this->_fieldMetaIndex = array();
      foreach( $meta as $metum ) {
        $this->_fieldMetaIndex[$metum->field_id] = $metum;
      }
    }
    return $this->_fieldMetaIndex;
  }

  public function getMetaById($id)
  {
    if( null === $this->_fieldMetaIndex ) {
      $this->getMetaAssoc();
    }

    if( !isset($this->_fieldMetaIndex[$id]) ) {
      return null;
    }

    return $this->_fieldMetaIndex[$id];
  }



  // Admin

  public function createMeta(array $params)
  {
    // Check data
    if( empty($params) || empty($params['type']) ) {
      throw new Fields_Model_Exception('Empty data');
    }

    // Check if parent option exists
    if( !empty($params['option_id']) && $params['option_id'] !== '0' ) {
      $option = Engine_Api::_()->fields()->getFieldsOptions($this->getFieldType())->getRowMatching('option_id', $params['option_id']);
      if( !$option ) {
         throw new Fields_Model_Exception('No option to add to');
      }
    } else {
      $params['option_id'] = '0';
      $option = null;
    }
    unset($params['option_id']);

    
    // Get info
    $info = Engine_Api::_()->fields()->getFieldInfo($params['type']);

    // Set some other stuff
    if( @$info['category'] != 'generic' ) {
      $params['alias'] = $params['type'];
    }

    // Default field multioptions
    $multiOptions = null;
    if( !empty($info['multiOptions']) && !empty($info['importOptions']) ) {
      $multiOptions = $info['multiOptions'];
    }

    // Create field
    $row = $this->getMeta()->createRow();

    // Diff
    $params['config'] = array_diff_key($params, $row->toArray()); // Get keys not in row for config

    // Save
    $row->setFromArray($params);
    $row->save();

    // Link field
    $mapsTable = Engine_Api::_()->fields()->getTable($this->getFieldType(), 'maps');
    $map = $mapsTable->createMap($row, $option);

    // Add default multioptions
    $optionsTable = Engine_Api::_()->fields()->getTable($this->getFieldType(), 'options');
    if( !empty($multiOptions) ) {
      foreach( $multiOptions as $label ) {
        $optionsTable->createOption($row, $label);
      }
    }

    // Do search
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'search')->checkSearchIndex($row);

    // Events
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onFieldMetaCreate', $row);
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onFieldMetaCreate_' . $this->getFieldType(), $row);

    // Update cache
    //$this->_setCache($this->_fieldMeta);
    // Eh, just flush cache
    $this->_flushCache();

    return $row;
  }

  public function editMeta($field, $params)
  {
    if( !($field instanceof Fields_Model_Meta) ) {
       throw new Fields_Model_Exception('Not a field');
    }

        // Get info
    $info = Engine_Api::_()->fields()->getFieldInfo($params['type']);

    // Set some other stuff
    if( @$info['category'] != 'generic' ) {
      $params['alias'] = $params['type'];
    } else {
      $params['alias'] = "";
    }

    // Diff
    $params['config'] = array_diff_key($params, $field->toArray()); // Get keys not in row for config
    //die(print_r($params,true));
    $field->setFromArray($params);
    $field->save();

    // Do search
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'search')->checkSearchIndex($field);

    // Events
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onFieldMetaEdit', $field);
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onFieldMetaEdit_' . $this->getFieldType(), $field);

    // Update cache
    //$this->_setCache($this->_fieldMeta);
    // Eh, just flush cache
    $this->_flushCache();

    return $field;
  }

  public function deleteMeta($field)
  {
    if( !($field instanceof Fields_Model_Meta) ) {
       throw new Fields_Model_Exception('Not a field');
    }

    // Delete associated stuff
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'maps')->deleteFieldMaps($field);
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'options')->deleteFieldOptions($field);
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'values')->deleteFieldValues($field);
    Engine_Api::_()->fields()->getTable($this->getFieldType(), 'search')->deleteFieldSearch($field);

    // Delete field
    $field->delete();

    // Flush cache
    $this->_flushCache();

    return $this;
  }
}