<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Row.php 7904 2010-12-03 03:36:14Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Db_Table_Row extends Zend_Db_Table_Row_Abstract implements Iterator
{
  public function __construct(array $config)
  {
    // @todo Technically, we should have this run after serialization because
    // init will get called before unserialization
    parent::__construct($config);
      
    // Unserialize rows
    if( $this->_table instanceof Engine_Db_Table &&
        null !== ($cols = $this->_table->getSerializedColumns()) )
    {
      foreach( $cols as $colName ) {
        if( !empty($this->_data[$colName]) &&
            is_scalar($this->_data[$colName]) &&
            false != ($val = Zend_Json::decode($this->_data[$colName])) &&
            $val != $this->_data[$colName] ) {
          $this->_data[$colName] = $val;
        }
      }
    }
  }
  
  /* Inteface: Iterator */
  
  function rewind()
  {
    reset($this->_data);
  }

  function current()
  {
    return current($this->_data);
  }

  function key()
  {
    return key($this->_data);
  }

  function next()
  {
    next($this->_data);
  }

  function valid()
  {
    return (current($this->_data) !== false);
  }
}