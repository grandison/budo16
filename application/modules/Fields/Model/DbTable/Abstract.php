<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
abstract class Fields_Model_DbTable_Abstract extends Engine_Db_Table
{
  protected $_fieldType;

  protected $_fieldTableType;

  protected $_rowsetClass = 'Fields_Model_Rowset';
  
  static public function factory($itemType, $tableType, $config = array())
  {
    if( !is_string($itemType) || !is_string($tableType) ) {
      throw new Fields_Model_Exception('Item type and table type must be strings');
    }
    $class = 'Fields_Model_DbTable_' . ucfirst($tableType);
    Engine_Loader::loadClass($class);
    return new $class($itemType, $tableType, $config);
  }

  public function __construct($itemType, $tableType, $config = array())
  {
    if( !is_string($itemType) || !is_string($tableType) ) {
      throw new Fields_Model_Exception('Item type and table type must be strings');
    }
    $this->_fieldType = $itemType;
    $this->_fieldTableType = $tableType;
    $config['name'] = $itemType . '_fields_' . $tableType;
    parent::__construct($config);
  }

  public function getFieldType()
  {
    if( null === $this->_fieldType ) {
      throw new Fields_Model_Exception('Type must be a string');
    }
    return $this->_fieldType;
  }

  public function getFieldTableType()
  {
    if( null === $this->_fieldTableType ) {
      $this->_fieldTableType = strtolower(trim(strrchr(get_class($this), '_'), '_'));
    }
    return $this->_fieldTableType;
  }



  // Caching stuff

  public function flushCache()
  {
    $this->_flushCache();
    return $this;
  }

  protected function _setCache($data)
  {
    if( Zend_Registry::isRegistered('Zend_Cache') &&
      ($cache = Zend_Registry::get('Zend_Cache')) instanceof Zend_Cache_Core ) {
      return $cache->save($data, get_class($this) . '__' . $this->_fieldType);
    }
  }

  protected function _getCache()
  {
    if( Zend_Registry::isRegistered('Zend_Cache') &&
      ($cache = Zend_Registry::get('Zend_Cache')) instanceof Zend_Cache_Core ) {
      return $cache->load(get_class($this) . '__' . $this->_fieldType);
    }
  }

  protected function _flushCache()
  {
    if( Zend_Registry::isRegistered('Zend_Cache') &&
      ($cache = Zend_Registry::get('Zend_Cache')) instanceof Zend_Cache_Core ) {
      return $cache->remove(get_class($this) . '__' . $this->_fieldType);
    }
  }
}