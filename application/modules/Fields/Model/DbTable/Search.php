<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Search.php 8790 2011-04-05 22:08:54Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_DbTable_Search extends Fields_Model_DbTable_Abstract
{
  protected $_fieldSearch = array();

  protected $_rowClass = 'Fields_Model_Search';

  public function getSearch($item)
  {
    $id = $this->_getIdentity($item);

    if( !array_key_exists($id, $this->_fieldSearch) ) {
      $this->_fieldSearch[$id] = $this->fetchRow($this->select()->where('item_id = ?', $id));
    }

    return $this->_fieldSearch[$id];
  }

  public function clearValues()
  {
    $this->_fieldSearch = array();
    return $this;
  }

  public function getSearchSelect($params)
  {
    $select = $this->select();
    $parts = $this->getSearchQuery($params);
    foreach( $parts as $w => $v ) {
      $select->where($w, $v);
    }
    return $select;
  }

  public function getSearchQuery($params)
  {
    $colsMeta = $this->info('metadata');
    $metaData = Engine_Api::_()->fields()->getFieldsMeta($this->getFieldType());

    $parts = array();
    foreach( $params as $key => $value ) {
      if( !isset($colsMeta[$key]) ) continue;
      $colMeta = $colsMeta[$key];

      // Ignore empty values
      if( (is_scalar($value) && $value === '') ||
          (is_array($value) && empty($value)) ||
          (is_array($value) && array_key_exists('min', $value) && array_filter($value) === array() ) ) {
        continue;
      }

      // Hack for age->birthdate
      if( $key == 'birthdate' || $key == 'birthday' ) {
        if( is_array($value) &&  $value['min'] != $value['max'] ) {
          $min = null;
          $max = null;

          if( !empty($value['min']) ) {
            $max = date('Y-m-d', (time() - (365 * 24 * 60 * 60) * $value['min']));
          }
          unset($value['min']);

          if( !empty($value['max']) ) {
            $min = date('Y-m-d', (time() - (365 * 24 * 60 * 60) * $value['max'])
                + (365 * 24 * 60 * 60)); // Hack for max-age year
          }
          unset($value['max']);

          if( $min ) {
            $value['min'] = $min;
          }
          if( $max ) {
            $value['max'] = $max;
          }
        } else if( is_scalar($value) || $value['min'] == $value['max'] ) {
          if (!is_scalar($value)) $value = $value['min'];
          $value = array(
            'min' => date('Y-m-d', (time() - (365 * 24 * 60 * 60) * ($value + 1) - 1)),
            'max' => date('Y-m-d', (time() - (365 * 24 * 60 * 60) * $value )),
          );
        }
      }

      // Set
      if( strtoupper(substr($colMeta['DATA_TYPE'], 0, 3)) === 'SET' ) {
        preg_match('/\((.+)\)/', $colMeta['DATA_TYPE'], $m);
        if( empty($m[1]) ) continue;
        $allowed = $m[1];
        $allowed = explode(',', $m[1]);
        foreach( $allowed as &$al ) {
          $al = trim($al, '\'",');
        }
        $value = (array) $value;
        $value = array_intersect($allowed, $value);
        if( empty($value) ) continue;
        $value = '%' . join('%', $value) . '%';

        $parts[$key . ' LIKE ?'] = $value;
      }
      
      // Range
      else if( is_array($value) && (array_key_exists('min', $value) || array_key_exists('max', $value)) ) {
        if( isset($value['min']) && $value['min'] !== '' ) {
          $parts[$key . ' >= ?'] = $value['min'];
        }
        if( isset($value['max']) && $value['max'] !== '' ) {
          $parts[$key . ' <= ?'] = $value['max'];
        }
      }

      // Substring?
      // @todo don't really like this
      else if( is_string($value) && ($value[0] == '%' || $value[strlen($value)-1] == '%') ) {
        $parts[$key . ' LIKE ?'] = $value;
      }

      // Scalar
      else if( is_scalar($value) ) {
        $parts[$key . ' = ?'] = $value;
      }
    }

    return $parts;
  }

  public function updateSearch($spec, $values)
  {
    if( !($spec instanceof Core_Model_Item_Abstract) ) {
      throw new Fields_Model_Exception('Not an item');
    }
    if( !($values instanceof Zend_Db_Table_Rowset_Abstract) ) {
      return null;
    }

    // Prepare data
    $cols = $this->info('cols');
    $colsMeta = $this->info('metadata');
    $metaData = Engine_Api::_()->fields()->getFieldsMeta($spec);
    $searchRow = $this->getSearch($spec);
    if( null === $searchRow ) {
      $this->_fieldSearch[$spec->getIdentity()] = $searchRow = $this->createRow();
      $searchRow->item_id = $spec->getIdentity();
    }

    // Index
    $indexedValues = array();
    foreach( $values as $row ) {
      $searchField = $metaData->getRowMatching('field_id', $row->field_id);
      if( !$searchField ) continue;
      $searchCol = $this->_getSearchColumn($searchField);
      if( !in_array($searchCol, $cols) ) continue;

      if( !isset($indexedValues[$searchCol]) ) {
        $indexedValues[$searchCol] = $row->value;
      } else {
        // Fixes #1393 courtesy of RadCodes
        if( !is_array($indexedValues[$searchCol]) ) {
          $indexedValues[$searchCol] = array($indexedValues[$searchCol]);
        }
        $indexedValues[$searchCol][] = $row->value;
      }
    }

    // Update
    foreach( $indexedValues as $col => $value ) {
      $colMeta = $colsMeta[$col];
      if( strtoupper(substr($colMeta['DATA_TYPE'], 0, 3)) === 'SET' ) {
        preg_match('/\((.+)\)/', $colMeta['DATA_TYPE'], $m);
        if( empty($m[1]) ) continue;
        $allowed = $m[1];
        $allowed = explode(',', $m[1]);
        foreach( $allowed as &$al ) {
          $al = trim($al, '\'",');
        }

        $value = (array) $value;
        $value = array_intersect($allowed, $value);
        $value = join(',', $value);
      } else if( is_array($value) ){
        $value = array_shift(array_filter($value));
      }
      $searchRow->$col = $value;
    }

    $searchRow->save();

    return $searchRow;
  }

  public function removeItemValues($item)
  {
    if( !($item instanceof Core_Model_Item_Abstract) ) {
      throw new Fields_Model_Exception('Not an item');
    }
    
    $this->delete(array(
      'item_id = ?' => $item->getIdentity(),
    ));

    return $this;
  }

  public function checkSearchIndex($field)
  {
    // Note: this is called on create and edit

    // Note: and now delete
    if( !$field->search ) {
      $this->deleteFieldSearch($field);
      return $this;
    }


    // Prepare table info
    $name = $this->info('name');
    $cols = $this->info('cols');

    // Make column name
    $searchCol = null;
    if( !empty($field->alias) ) {
      $searchCol = $field->alias;
    } else {
      $searchCol = sprintf('field_%d', $field->field_id);
    }

    // Get sql params
    $sqlParams = null;
    
    // Pull sql params from field class
    $class = 'Fields_Form_Element_'.Engine_Api::_()->fields()->inflectFieldType($field->type);
    if( @class_exists($class) && method_exists($class, 'getSearchSqlParams') ) {
      $sqlParams = call_user_func(array($class, 'getSearchSqlParams'));
    }

    // Infer sql params from field type
    else {
      $info = Engine_Api::_()->fields()->getFieldInfo($field->type);
      $genericType = null;
      if( !empty($info['base']) ) {
        $genericType = $info['base'];
      } else {
        $genericType = $field->type;
      }
      switch( $genericType ) {
        case 'text':
          $sqlParams = array(
            'type' => 'varchar',
            'length' => '255',
            'charset' => 'default',
            'collate' => 'default',
          );
          break;
        case 'textarea':
          $sqlParams = array(
            //'type' => 'text',
            'type' => 'varchar',
            'length' => '255',
            'charset' => 'default',
            'collate' => 'default',
          );
          break;
        case 'select':
        case 'radio':
          // Note: this won't work for options that have text keys (some of the pre-made ones)
          $option_ids = $field->getOptionIds();
          if( !empty($option_ids) ) {
            $sqlParams = array(
              'type' => 'enum',
              'length' => $option_ids,
            );
          } else if( !empty($info['multiOptions']) ) {
            $sqlParams = array(
              'type' => 'enum',
              'length' => array_keys($info['multiOptions']),
            );
          } else {
            $sqlParams = array(
              'type' => 'varchar',
              'length' => '255',
              'charset' => 'default',
              'collate' => 'default',
            );
          }
          break;
        case 'multiselect':
        case 'multi_checkbox':
          $option_ids = $field->getOptionIds();
          if( !empty($option_ids) ) {
            $sqlParams = array(
              'type' => 'set',
              'length' => $option_ids,
            );
          } else if( !empty($info['multiOptions']) ) {
            $sqlParams = array(
              'type' => 'set',
              'length' => array_keys($info['multiOptions']),
            );
          } else {
            $sqlParams = array(
              'type' => 'varchar',
              'length' => '255',
              'charset' => 'default',
              'collate' => 'default',
            );
          }
          break;
        case 'checkbox':
          $sqlParams = array(
            'type' => 'tinyint',
            'length' => 1,
          );
          break;
        case 'integer':
          $sqlParams = array(
            'type' => 'int',
          );
          break;
        case 'float':
          $sqlParams = array(
            'type' => 'float',
          );
          break;
        case 'date':
          $sqlParams = array(
            'type' => 'date',
          );
          break;
        case 'datetime':
          $sqlParams = array(
            'type' => 'datetime',
          );
          break;
      }
    }

    // Exit if no params
    if( empty($sqlParams) ) {
      return $this;
    }

    
    // Action
    $sqlParams['column'] = $searchCol;

    // Create
    $exists = false;
    $tmp_e = null;
    if( !in_array($searchCol, $cols) ) {
      try {
        $alterSql = $this->_constructAlterQuery($sqlParams, 'create');
        $indexSql = $this->_constructIndexQuery($sqlParams, 'create');
        
        $this->getAdapter()->query($alterSql);
        $this->getAdapter()->query($indexSql);

        $this->flushMetaData();

        $exists = true;

      } catch( Exception $e ) {
        $tmp_e = $e;
        throw $e; // Debug
      }
    }

    // Edit
    else {
      try {
        $sql = $this->_constructAlterQuery($sqlParams, 'edit');
        $this->getAdapter()->query($sql);
        $sql = $this->_constructIndexQuery($sqlParams, 'edit');
        $this->getAdapter()->query($sql);

        $this->flushMetaData();

        $exists = true;
        
      } catch( Exception $e ) {
        $tmp_e = $e;
        throw $e; // Debug
      }
    }

    // Check if exception was throw during alter
    if( !$exists ) {
      $field->search = 0;
      $field->save();
      if( $tmp_e instanceof Exception ) throw $tmp_e;
    }

    return $this;
  }

  public function deleteFieldSearch($field)
  {
    // Note: this is called on delete (duh)

    // Note: and also on check
    if( $field->search ) {
      return $this;
    }


    // Prepare table info
    $name = $this->info('name');
    $cols = $this->info('cols');

    // Make column name
    $searchCol = null;
    if( !empty($field->alias) ) {
      $searchCol = $field->alias;
    } else {
      $searchCol = sprintf('field_%d', $field->field_id);
    }

    // Don't delete if any other column with the same alias exist
    if( !empty($field->alias) ) {
      $sameAliasedFields = Engine_Api::_()->fields()->getFieldsMeta($this->getFieldType())->getRowsMatching('alias', $field->alias);
      if( count($sameAliasedFields) > 1 ) {
        return $this;
      }
    }
    
    // Only delete if the col exists (duh)
    if( in_array($searchCol, $cols) ) {

      try {
        $sql = $this->_constructIndexQuery($searchCol, 'delete');
        $this->getAdapter()->query($sql);
        $sql = $this->_constructAlterQuery($searchCol, 'delete');
        $this->getAdapter()->query($sql);

        $this->flushMetaData();

      } catch( Exception $e ) {
        $exists = true;
        $tmp_e = $e;
        throw $e; // Debug
      }

    }

    return $this;
  }

  public function flushOptionSearch($option)
  {
    
  }


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

  protected function _constructAlterQuery($params, $method = 'create')
  {
    // Construct
    $sql = '';
    $name = $this->info('name');

    // Check column
    $column = null;
    if( is_string($params) ) {
      $column = $params;
    } else if( isset($params['column']) && is_string($params['column']) ) {
      $column = $params['column'];
    }

    if( !$column || ($method !== 'delete' && !is_array($params)) ) {
      throw new Fields_Model_Exception('Invalid alter params');
    }
    
    // Only main part for create/edit
    if( $method != 'delete' ) {

      // Check params
      if( empty($params['type']) || !is_string($params['type']) ) {
        throw new Fields_Model_Exception('Invalid alter params');
      }

      // Column type
      $sql .= $params['type'];

      // Column length
      if( !empty($params['length']) ) {
        if( is_array($params['length']) ) {
          $sql .= '(\'' . join("','", $params['length']) . '\')';
        } else if( is_numeric($params['length']) || is_string($params['length']) ) {
          $sql .= '('.$params['length'].')';
        } else {
          // ignore for now
        }
      }

      // Column charset
      if( !empty($params['charset']) ) {
        if( $params['charset'] == 'default' || $params['charset'] === true ) {
          $sql .= ' CHARACTER SET utf8';
        } else if( is_string($params['charset']) ) {
          $sql .= ' CHARACTER SET ' . $params['charset'];
        }
      }

      // Column collate
      if( !empty($params['collate']) ) {
        if( $params['collate'] == 'default' || $params['collate'] === true ) {
          $sql .= ' COLLATE utf8_unicode_ci';
        } else if( is_string($params['collate']) ) {
          $sql .= ' COLLATE ' . $params['collate'];
        }
      }

      // Column null
      $sql .= ' ' . ( !isset($params['null']) || $params['null'] ? 'NULL' : 'NOT NULL' );

      // Column default
      if( isset($params['default']) ) {
          $sql .= ' default \''.$params['default'].'\'';
      } else {
        if( isset($params['null']) && !$params['null'] ) {
          $sql .= ' default \'\'';
        }
      }

    }

    // Make full query
    switch( $method ) {
      case 'create':
        $sql = "ALTER TABLE `{$name}` ADD COLUMN `$column` {$sql}";
        break;
      case 'edit':
        $sql = "ALTER TABLE `{$name}` CHANGE COLUMN `$column` `$column` {$sql}";
        break;
      case 'delete':
        $sql = "ALTER TABLE `{$name}` DROP COLUMN `$column`";
        break;
      default:
        throw new Fields_Model_Exception('invalid operation');
        break;
    }

    return $sql;
  }

  protected function _constructIndexQuery($column, $method = 'create')
  {
    if( is_array($column) && !empty($column['column']) && is_string($column['column']) ) {
      $column = $column['column'];
    } else if( !is_string($column) ) {
      throw new Fields_Model_Exception('invalid column name');
    }

    $sql = '';
    $name = $this->info('name');
    
    switch( $method ) {
      case 'create':
        $sql = "ALTER TABLE `{$name}` ADD INDEX ( `{$column}` )";
        break;
      case 'edit':
        // Do we need to do this?
        $sql = "ALTER TABLE `{$name}` DROP INDEX `{$column}`,  ADD INDEX `{$column}` ( `{$column}` )";
        break;
      case 'delete':
        $sql = "ALTER TABLE `{$name}` DROP INDEX `{$column}`";
        break;
      default:
        throw new Fields_Model_Exception('invalid operation');
        break;
    }
    
    return $sql;
  }

  protected function _getSearchColumn($field)
  {
    if( !($field instanceof Fields_Model_Meta) ) {
      throw new Fields_Model_Exception('not a field');
    } else if( !empty($field->alias) ) {
      return $field->alias;
    } else {
      return sprintf('field_%d', $field->field_id);
    }
  }
}