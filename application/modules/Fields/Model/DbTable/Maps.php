<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Maps.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_DbTable_Maps extends Fields_Model_DbTable_Abstract
{
  protected $_fieldMaps;

  protected $_fieldMapsIndex;

  protected $_rowClass = 'Fields_Model_Map';
  
  public function getMaps()
  {
    if( null === $this->_fieldMaps ) {
      if( ($data = $this->_getCache()) instanceof Zend_Db_Table_Rowset_Abstract ) {
        $this->_fieldMaps = $data;
      } else {
        $this->_fieldMaps = $this->fetchAll($this->select()->order('order'));
        $this->_setCache($this->_fieldMaps);
      }
    }
    return $this->_fieldMaps;
  }

  public function getMapsAssoc()
  {
    if( null === $this->_fieldMapsIndex ) {
      $maps = $this->getMaps();
      $this->_fieldMapsIndex = array();
      foreach( $maps as $map ) {
        $this->_fieldMapsIndex[$map->option_id][$map->field_id] = $map;
      }
    }

    return $this->_fieldMapsIndex;
  }

  public function getMapsById($option_id, $field_id = null)
  {
    if( null === $this->_fieldMapsIndex ) {
      $this->getMapsAssoc();
    }

    if( null !== $field_id ) {
      if( isset($this->_fieldMapsIndex[$option_id][$field_id]) ) {
        return $this->_fieldMapsIndex[$option_id][$field_id];
      } else {
        return null;
      }
    } else {
      if( isset($this->_fieldMapsIndex[$option_id]) ) {
        return $this->_fieldMapsIndex[$option_id];
      } else {
        return null;
      }
    }
  }

  public function createMap($field, $option = null)
  {
    if( !($field instanceof Fields_Model_Meta)  ) {
      throw new Fields_Model_Exception('Invalid arguments');
    }

    if( null !== $option && !($option instanceof Fields_Model_Option) ) {
      throw new Fields_Model_Exception('Invalid arguments');
    }

    $field_id = ( is_object($option) ? $option->field_id : '0' );
    $option_id = ( is_object($option) ? $option->option_id : '0' );

    // Create map
    $map = $this->getMaps()->createRow();
    $map->field_id = $field_id;
    $map->option_id = $option_id;
    $map->child_id = $field->field_id;
    $map->order = 9999;
    $map->save();

    // Update cache
    //$this->_setCache($this->_fieldMaps);
    // Eh, just flush cache
    $this->_flushCache();
    
    return $map;
  }

  public function deleteMap($map)
  {
    // Check if we should delete the field?
    $count = 0;
    foreach( $this->getMaps() as $checkMap ) {
      if( $checkMap->child_id == $map->child_id ) {
        $count++;
      }
    }
    $mapInfo = $map->toArray();

    // Delete the map
    $map->delete();

    // Delete the field?
    if( $count <= 1 ) {
      Engine_Api::_()->fields()->deleteField($this->getFieldType(), $mapInfo['child_id']);
    }

    $this->_flushCache();

    return $this;
  }

  public function deleteFieldMaps($field)
  {
    foreach( $this->getMaps() as $map ) {
      if( $map->field_id == $field->field_id ) {
        $this->deleteMap($map);
      } else if( $map->child_id == $field->field_id ) {
        $this->deleteMap($map);
      }
    }

    // Flush local maps
    $this->_fieldMaps = null;
    $this->_fieldMapsIndex = null;

    return $this;
  }
}
