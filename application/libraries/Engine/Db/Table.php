<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Table.php 8483 2011-02-17 00:05:49Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Db_Table extends Zend_Db_Table_Abstract
{
  // Properties
  
  /**
   * The string to prefix all table names with
   * 
   * @var string
   */
  protected static $_tablePrefix = '';

  /**
   * The class to use as a row object
   * 
   * @var string 
   */
  protected $_rowClass = 'Engine_Db_Table_Row';

  /**
   * The class to use as a rowset object
   *
   * @var string 
   */
  protected $_rowsetClass = 'Engine_Db_Table_Rowset';
  
  /**
   * An array of columns set for automatic serialization
   * 
   * @var array
   */
  //protected $_serializedColumns;



  // Static

  /**
   * Set the table name prefix
   * 
   * @param string $prefix
   */
  public static function setTablePrefix($prefix)
  {
    self::$_tablePrefix = $prefix;
  }

  /**
   * Get the table name prefix
   * 
   * @return string
   */
  public static function getTablePrefix()
  {
    return self::$_tablePrefix;
  }


  /* General */

  /**
   * Same as find, but only returns a single row
   * 
   * @param mixed $id
   * @return Engine_Db_Table_Row
   */
  public function findRow($id)
  {
    $data = $this->find($id);
    return $data[0];
    
    $this->_setupPrimaryKey();
    $primary = $this->_primary[0];
    $where = $primary . ' = ' . $this->getAdapter()->quote($id);
    return $this->fetchRow($this->select()->where($where));
  }

  public function flushMetaData()
  {
    $this->_primary = null;
    $this->_metadata = array(); // Have to flush metadata after alter
    $this->_cols = null;
    
    return $this;
  }

  /**
   * Injects table prefix
   */
  protected function _setupTableName()
  {
    // Name mapping:
    // Core_Model_DbTable_Content -> {prefix}core_content
    // Core_Model_DbTable_Content_Manager -> {prefix}core_content_manager
    if( !$this->_name )
    {
      $this->_name = get_class($this);
      $this->_name = str_replace('_model_dbtable_', '_', strtolower($this->_name));
    }
    else if( strpos($this->_name, '.') )
    {
      list($this->_schema, $this->_name) = explode('.', $this->_name);
    }

    // Prepend prefix
    $this->_name = self::$_tablePrefix . $this->_name;
  }



  // Serialize

  /**
   * Inject automatic serialization logic
   * 
   * @param array $data
   * @return void
   */
  public function insert(array $data)
  {
    return parent::insert($this->_serializeColumns($data));
  }

  /**
   * Inject automatic serialization logic
   * 
   * @param  array        $data  Column-value pairs.
   * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
   * @return int          The number of rows updated.
   */
  public function update(array $data, $where)
  {
    return parent::update($this->_serializeColumns($data), $where);
  }

  /*
  protected function _fetch(Zend_Db_Table_Select $select)
  {
    // Decrypt each column
    $rows = parent::_fetch($select);
    foreach( $rows as $index => $data ) {
      $rows[$index] = $this->_unserializeColumns($data);
    }

    return $rows;
  }
   * 
   */

  /**
   * Get array of columns that are set for automatic serialization
   * 
   * @return array
   */
  public function getSerializedColumns()
  {
    if( empty($this->_serializedColumns) )
    {
      return null;
    }
    
    return $this->_serializedColumns;
  }

  protected function _serializeColumns(array $data)
  {
    if( null !== ($cols = $this->getSerializedColumns()) ) {
      foreach( $cols as $col ) {
        if( isset($data[$col]) &&
            !is_scalar($data[$col]) &&
            !($data[$col] instanceof Zend_Db_Expr) ) {
          $data[$col] = Zend_Json::encode($data[$col]);
        }
      }
    }
    
    return $data;
  }
  
  protected function _unserializeColumns(array $data)
  {
    if( null !== ($cols = $this->getSerializedColumns()) ) {
      foreach( $cols as $col ) {
        if( isset($data[$col]) &&
            is_scalar($data[$col]) &&
            false != ($val = Zend_Json::decode($data[$col])) &&
            $val != $data[$col] ) {
          $data[$col] = $val;
        }
      }
    }
    
    return $data;
  }
}