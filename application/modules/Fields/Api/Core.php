<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7318 2010-09-08 05:30:40Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Api_Core extends Core_Api_Abstract
{
  // Properties

  /**
   * @var array An array of table objects
   */
  protected $_tables = array();

  /**
   * @var array Contains information about the various field types
   */
  protected $_fieldTypeInfo;


  // Tables

  /**
   * Gets a typed table class
   *
   * @param string $type The item type, i.e. user
   * @param string $name The name of the table, i.e. fields, map, options, values
   * @return Engine_Db_Table
   */
  public function getTable($type, $name)
  {
    $type = $this->getFieldType($type);
    
    if( !isset($this->_tables[$type][$name]) )
    {
      $this->_tables[$type][$name] = Fields_Model_DbTable_Abstract::factory($type, $name);
    }

    return $this->_tables[$type][$name];
  }



  // Data

  /**
   * Gets the rowset of field-option mapping for the specified field system class
   *
   * @param Core_Model_Item_Abstract|string $spec The field system class
   * @return Engine_Db_Table_Rowset
   */
  public function getFieldsMaps($type)
  {
    return $this->getTable($this->getFieldType($type), 'maps')->getMaps();
  }
  
  /**
   * Gets the rowset of field metadata for the specified field system class
   *
   * @param Core_Model_Item_Abstract|string $spec The field system class
   * @return Engine_Db_Table_Rowset
   */
  public function getFieldsMeta($type)
  {
    return $this->getTable($this->getFieldType($type), 'meta')->getMeta();
  }

  /**
   * Gets the rowset of option metadata for the specified field system class
   *
   * @param Core_Model_Item_Abstract|string $spec The field system class
   * @return Engine_Db_Table_Rowset
   */
  public function getFieldsOptions($type)
  {
    return $this->getTable($this->getFieldType($type), 'options')->getOptions();
  }

  /**
   * Gets the search index table row for the specified item
   * 
   * @param Core_Model_Item_Abstract $spec
   * @return Engine_Db_Table_Row|null
   */
  public function getFieldsSearch($spec)
  {
    return $this->getTable($this->getFieldType($spec), 'search')->getSearch($spec);
  }

  /**
   * Gets the rowset of value data for the specified item
   *
   * @param Core_Model_Item_Abstract $spec The field system class
   * @return Engine_Db_Table_Rowset
   */
  public function getFieldsValues($spec)
  {
    return $this->getTable($this->getFieldType($spec), 'values')->getValues($spec);
  }

  public function removeItemValues($spec)
  {
    $this->getTable($this->getFieldType($spec), 'values')->removeItemValues($spec);
    $this->getTable($this->getFieldType($spec), 'search')->removeItemValues($spec);
    return $this;
  }

  public function getMatchingItems($type, $field, $pattern, $match = null)
  {
    return Engine_Api::_()->getItemMulti($type, $this->getMatchingItemIds($type, $field, $pattern, $match));
  }

  public function getMatchingItemIds($type, $field, $pattern, $match = null)
  {
    $type = $this->getFieldType($type);
    $field = $this->getField($field, $type);

    // Mmm hacking
    if( $match == 'date' ) {
      $match = 'range';
    }

    // If match not given, try to infer pattern type
    if( null === $match ) {
      // range
      if( is_array($pattern) && (isset($match['min']) || isset($match['max'])) ) {
        $match = 'range';
      }
      // list
      else if( is_array($pattern) ) {
        $match = 'list';
      }
      // exact
      else if( is_scalar($pattern) ) {
        if( is_string($pattern) && trim($pattern, '%') != $pattern ) {
          $match = 'like';
        } else {
          $match = 'exact';
        }
      }
      // unknown
      else {
        throw new Fields_Model_Exception('Unknown pattern type: ' . gettype($pattern));
      }
    } else if( !in_array($match, array('range', 'list', 'exact')) ) {
      throw new Fields_Model_Exception('Unknown match type: ' . $match);
    }

    // Prepare query
    $table = $this->getTable($type, 'values');
    $select = new Zend_Db_Select($table->getAdapter());
    $select
      ->from($table->info('name'), 'item_id')
      ->where('field_id = ?', $field->field_id);

    // Try to do preliminary matching
    if( $match == 'exact' ) {
      $select->where('value = ?', $pattern);
    } else if( $match == 'like' ) {
      $select->where('value LIKE ?', $pattern);
    } else if( $match == 'list' ) {
      $select->where('value IN(?)', array_values($pattern));
    } else if( $match == 'range' ) {
      if( !empty($pattern['min']) ) {
        $select->where('value >= ?', $pattern['min']);
      }
      if( !empty($pattern['max']) ) {
        $select->where('value >= ?', $pattern['max']);
      }
    } else {
      throw new Fields_Model_Exception('Unknown match type: ' . $match);
    }

    // Get data
    $ids = array();
    $ids_raw = $select->query()->fetchAll();
    foreach( $ids_raw as $id_raw ) {
      $ids[] = $id_raw['item_id'];
    }

    return $ids;
  }


  
  // Structures

  /**
   * Generates a flattened array structure of all fields (generally for signup)
   *
   * @param Core_Model_Item_Abstract|string $spec The field system class
   * @param int $parent The field id to generate the structure from
   * @return array
   */
  public function getFieldsStructureFull($spec, $parent_field_id = null, $parent_option_id = null)
  {
    $type = $this->getFieldType($spec);

    $structure = array();
    foreach( $this->getFieldsMaps($type)->getRowsMatching('field_id', (int) $parent_field_id) as $map ) {
      // Skip maps that don't match parent_option_id (if provided)
      if( null !== $parent_option_id && $map->option_id != $parent_option_id ) {
        continue;
      }
      // Get child field
      $field = $this->getFieldsMeta($type)->getRowMatching('field_id', $map->child_id);
      if( empty($field) ) {
        continue;
      }
      // Add to structure
      $structure[$map->getKey()] = $map;
      // Get children
      if( $field->canHaveDependents() ) {
        $structure += $this->getFieldsStructureFull($spec, $map->child_id);
      }
    }

    return $structure;
  }

  /**
   * Generates a flattened array structure of only the fields that apply to the
   * specified item based on it's current values
   *
   * @param Core_Model_Item_Abstract $spec The item to use for generation
   * @param int $parent The field id to start with
   * @return array
   */
  public function getFieldsStructurePartial($spec, $parent_field_id = null)
  {
    // Spec must be a item for this one
    if( !($spec instanceof Core_Model_Item_Abstract) )
    {
      throw new Fields_Model_Exception("First argument of getFieldsValues must be an instance of Core_Model_Item_Abstract");
    }

    $type = $this->getFieldType($spec);
    $parentMeta = null;
    $parentValue = null;

    // Get current field values
    if( $parent_field_id ) {
      $parentMeta = $this->getFieldsMeta($type)->getRowMatching('field_id', $parent_field_id);
      $parentValueObject = $parentMeta->getValue($spec);
      if( is_array($parentValueObject) ) {
        $parentValue = array();
        foreach( $parentValueObject as $parentValueObjectSingle ) {
          $parentValue[] = $parentValueObjectSingle->value;
        }
      } else if( is_object($parentValueObject) ) {
        $parentValue = $parentValueObject->value;
      }
    }

    // Build structure
    $structure = array();
    foreach( $this->getFieldsMaps($spec)->getRowsMatching('field_id', (int) $parent_field_id) as $map ) {
      // Parent value does not match id
      if( $parent_field_id ) {
        if( !is_object($parentMeta) ) {
          continue;
        } else if( is_array($parentValue) && !in_array($map->option_id, $parentValue) ) {
          continue;
        } else if( null !== $parentValue && is_scalar($parentValue) && $parentValue != $map->option_id ) {
          continue;
        }
      }
      // Get child field
      $field = $this->getFieldsMeta($type)->getRowMatching('field_id', $map->child_id);
      if( empty($field) ) {
        continue;
      }
      // Add to structure
      $structure[$map->getKey()] = $map;
      // Get dependents
      if( $field->canHaveDependents() )
      {
        $structure += $this->getFieldsStructurePartial($spec, $field->field_id);
      }
    }
    
    return $structure;
  }

  /**
   * Returns an array of top-level fields (usually just the type)
   *
   * @param Core_Model_Item_Abstract|string $spec The field system class
   * @return array
   */
  public function getFieldsStructureParent($spec)
  {
    return $this->getFieldsMaps($spec)->getRowsMatching('field_id', 0);
  }

  public function getFieldsStructureSearch($spec, $parent_field_id = null, $parent_option_id = null, $showGlobal = true)
  {
    $type = $this->getFieldType($spec);

    $structure = array();
    foreach( $this->getFieldsMaps($type)->getRowsMatching('field_id', (int) $parent_field_id) as $map ) {
      // Skip maps that don't match parent_option_id (if provided)
      if( null !== $parent_option_id && $map->option_id != $parent_option_id ) {
        continue;
      }
      // Get child field
      $field = $this->getFieldsMeta($type)->getRowMatching('field_id', $map->child_id);
      if( empty($field) ) {
        continue;
      }
      // Add to structure
      if( $field->search ) {
        //if( $field->search == 2 && $showGlobal ) {
        //  $structure[$map->getKey()] = $map;
        //} else if( !$showGlobal ) {
          $structure[$map->getKey()] = $map;
        //}
      }

      // Get children
      if( $field->canHaveDependents() ) {
        $structure += $this->getFieldsStructureSearch($spec, $map->child_id, null, $showGlobal);
      }
    }

    return $structure;
  }

  public function getFieldStructureTop($spec)
  {
    $type = $this->getFieldType($spec);
    $structure = array();
    foreach( $this->getFieldsMaps($type)->getRowsMatching('field_id', 0) as $map ) {
      $structure[] = $map;
    }
    return $structure;
  }



  // Aliasing

  /**
   * Gets all of the current field values by alias / value
   * @param Core_Model_Item_Abstract $spec
   * @return array
   */
  public function getFieldsValuesByAlias(Core_Model_Item_Abstract $spec)
  {
    $values = array();
    $structure = $this->getFieldsStructurePartial($spec);
    foreach( $structure as $key => $map )
    {
      $meta = $this->getFieldsMeta($spec)->getRowMatching('field_id', $map->child_id);
      if( is_object($meta) && !empty($meta->alias) )
      {
        $value = $meta->getValue($spec);
        if( is_object($value) )
        {
          $values[$meta->alias] = $value->value;
        }
        else if( is_array($value) )
        {
          $vals = array();
          foreach( $value as $sval ) {
            if( is_object($sval) ) $vals[] = $sval->value;
          }
          $values[$meta->alias] = $vals;
        }
        else
        {
          $values[$meta->alias] = null;
        }
      }
    }

    return $values;
  }

  /**
   * Gets field objects by alias
   *
   * @param Core_Model_Item_Abstract $spec
   * @return array
   */
  public function getFieldsObjectsByAlias($spec, $alias = null)
  {
    $fields = array();
    
    if( $spec instanceof Core_Model_Item_Abstract ) {
      $structure = $this->getFieldsStructurePartial($spec);
    } else {
      $structure = $this->getFieldsStructureFull($spec);
    }

    foreach( $structure as $key => $map )
    {
      $meta = $this->getFieldsMeta($spec)->getRowMatching('field_id', $map->child_id);
      if( is_object($meta) && !empty($meta->alias) ) {
        if( null === $alias || $meta->alias == $alias ) {
          $fields[$meta->alias] = $meta;
        }
      }
    }

    return $fields;
  }



  // Edit

  public function createMap($field, $option)
  {
    $field = $this->getField($field);
    $option = $this->getOption($option, $field->getFieldType());
    
    return $this->getTable($field->getFieldType(), 'maps')->createMap($field, $option);
  }

  public function deleteMap($map)
  {
    if( !($map instanceof Fields_Model_Map) ) {
      throw new Fields_Model_Exception('Not a map');
    }
    $this->getTable($map->getFieldType(), 'maps')->deleteMap($map);
    return $this;
  }
  
  public function createField($type, $options)
  {
    return $this->getTable($this->getFieldType($type), 'meta')->createMeta($options);
  }

  /**
   * Edit a field
   *
   * @param string $type The field system class
   * @param Fields_Model_DbRow_Meta|int $field The row or id of the field
   * @param array $options The field options
   * @return Fields_Model_Api
   */
  public function editField($type, $field, $options)
  {
    $type = $this->getFieldType($type);
    $field = $this->getField($field, $type);
    return $this->getTable($type, 'meta')->editMeta($field, $options);
  }

  public function deleteField($type, $field)
  {
    $type = $this->getFieldType($type);
    $field = $this->getField($field, $type);

    $this->getTable($type, 'meta')->deleteMeta($field);

    return $this;
  }
  
  /**
   * Adds an option to a select-type field
   *
   * @param string $type The field system class
   * @param Fields_Model_DbRow_Meta|int $field The row or id of the field
   * @param array $options The options for the option
   * @return int The id of the new option
   * @throws Fields_Model_Exception If the field cannot have dependents
   */
  public function createOption($type, $field, $options)
  {
    $type = $this->getFieldType($type);
    $field = $this->getField($field, $type);
    return $this->getTable($type, 'options')->createOption($field, $options);
  }

  /**
   * Edit an option
   *
   * @param string $type The field system class
   * @param Fields_Model_DbRow_Meta|int $option The row or id of the option
   * @param array $options The option options
   * @return Fields_Model_Api
   */
  public function editOption($type, $option, $options)
  {
    $type = $this->getFieldType($type);
    $option = $this->getOption($option, $type);
    $this->getTable($type, 'options')->editOption($option, $options);
    return $this;
  }

  public function deleteOption($type, $option)
  {
    $type = $this->getFieldType($type);
    $option = $this->getOption($option, $type);
    $this->getTable($type, 'options')->deleteOption($option);
    return $this;
  }



  // Type stuff
  
  public function getFieldInfo($type = null, $value = null)
  {
    if( null === $this->_fieldTypeInfo ) {
      $this->_fieldTypeInfo = include APPLICATION_PATH . '/application/modules/Fields/settings/fields.php';
    }

    switch( $type ) {
      case null:
        return $this->_fieldTypeInfo;
        break;
      case 'categories':
        return $this->_fieldTypeInfo['categories'];
        break;
      case 'fields':
        return $this->_fieldTypeInfo['fields'];
        break;
      case 'dependents':
        return $this->_fieldTypeInfo['dependents'];
        break;
    }

    // Get base field info
    if( isset($this->_fieldTypeInfo['fields'][$type]) ) {
      $info = $this->_fieldTypeInfo['fields'][$type];
      if( !empty($info['base']) && !empty($this->_fieldTypeInfo['fields'][$info['base']]) ) {
        $info = array_merge($this->_fieldTypeInfo['fields'][$info['base']], $info);
      }
      if( null !== $value ) {
        if( isset($info[$value]) ) {
          return $info[$value];
        }
      } else {
        return $info;
      }
    }

    return null;
  }

  public function inflectFieldType($string)
  {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
  }


  // Search

  public function checkSearchIndex($field)
  {
    $this->getTable($field->getFieldType(), 'search')->checkSearchIndex($field);
    return $this;
  }

  public function updateSearch($spec, $values)
  {
    $type = $this->getFieldType($spec);
    return $this->getTable($type, 'search')->updateSearch($spec, $values);
  }

  public function getSearchQuery($spec, $params)
  {
    $type = $this->getFieldType($spec);
    return $this->getTable($type, 'search')->getSearchQuery($params);
  }

  public function getSearchSelect($spec, $params)
  {
    $type = $this->getFieldType($spec);
    return $this->getTable($type, 'search')->getSearchSelect($params);
  }


  // Utility

  /**
   * Get a field row
   *
   * @param Core_Model_Item_Abstract|string $type
   * @param Fields_Model_DbRow_Meta|int The row or id of the field
   * @return Fields_Model_DbRow_Meta
   * @throws Fields_Model_Exception If the row could not be found
   */
  public function getField($field, $type = null, $throw = true)
  {
    $type = $this->getFieldType($type, false);

    if( $field instanceof Fields_Model_Meta ) {
      if( null !== $type && $field->getFieldType() != $type ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Field type did not match passed type");
        } else {
          return null;
        }
      }
      return $field;
    }

    if( null === $type ) {
      if( $throw ) {
        throw new Fields_Model_Exception("No field type");
      } else {
        return null;
      }
    }

    if( is_numeric($field) ) {
      $field = $this->getFieldsMeta($type)->getRowMatching('field_id', $field);
      if( null === $field || !($field instanceof Fields_Model_Meta) ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Missing field");
        } else {
          return null;
        }
      }
      return $field;
    }

    if( $throw ) {
      throw new Fields_Model_Exception("Invalid field identifier");
    } else {
      return null;
    }
  }

  /**
   * Get a field row
   *
   * @param Core_Model_Item_Abstract|string $type
   * @param Fields_Model_DbRow_Option|int The row or id of the option
   * @return Fields_Model_DbRow_Option
   * @throws Fields_Model_Exception If the row could not be found
   */
  public function getOption($option, $type = null, $throw = true)
  {
    $type = $this->getFieldType($type, false);

    if( $option instanceof Fields_Model_Option ) {
      if( null !== $type && $option->getFieldType() != $type ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Option type did not match passed type");
        } else {
          return null;
        }
      }
      return $option;
    }

    if( null === $type ) {
      if( $throw ) {
        throw new Fields_Model_Exception("No option type");
      } else {
        return null;
      }
    }

    if( is_numeric($option) ) {
      $option = $this->getFieldsOptions($type)->getRowMatching('option_id', $option);
      if( null === $option || !($option instanceof Fields_Model_Option) ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Missing option");
        } else {
          return null;
        }
      }
      return $option;
    }

    if( $throw ) {
      throw new Fields_Model_Exception("Invalid option identifier");
    } else {
      return null;
    }
  }

  public function getMap($field, $option, $type = null, $throw = true)
  {
    $type = $this->getFieldType($type, false);
    
    if( $field instanceof Fields_Model_Meta ) {
      if( $type && $type != $field->getFieldType() ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Map type did not match passed type");
        } else {
          return null;
        }
      } else {
        $type = $field->getFieldType();
      }
      // Convert to int
      $field = $field->field_id;
    }
    
    if( $option instanceof Fields_Model_Option ) {
      if( $type && $type != $option->getFieldType() ) {
        if( $throw ) {
          throw new Fields_Model_Exception("Map type did not match passed type");
        } else {
          return null;
        }
      } else {
        $type = $option->getFieldType();
      }
      $option = $option->field_id;
    }

    if( !is_numeric($field) || !is_numeric($option) ) {
      if( $throw ) {
        throw new Fields_Model_Exception("missing option or field");
      } else {
        return null;
      }
    }

    $map = $this->getFieldsMaps($type)->getRowMatching(array('child_id' => $field, 'option_id' => $option));
    if( !($map instanceof Fields_Model_Map) ) {
      if( $throw ) {
        throw new Fields_Model_Exception("missing map");
      } else {
        return null;
      }
    }

    return $map;
  }
  
  /**
   * Simply returns the passed type, or the type of the item if an item
   *
   * @param Core_Model_Item_Abstract|string $type
   * @return string
   * @throws Fields_Model_Exception If the first argument is neither a string
   *   nor an instance of Core_Model_Item_Abstract
   */
  public function getFieldType($dat, $throw = true)
  {
    if( $dat instanceof Fields_Model_Abstract ) {
      return $dat->getFieldType();
    } else if( $dat instanceof Core_Model_Item_Abstract ) {
      return $dat->getType();
    } else if( is_string($dat) ) {
      return $dat;
    } else {
      if( $throw ) {
        throw new Fields_Model_Exception("Unable to get field type");
      } else {
        return null;
      }
    }
  }
}

