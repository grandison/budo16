<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Rowset.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_Rowset extends Engine_Db_Table_Rowset
{
  protected $_fieldType;

  protected $_fieldTableType;

  public function getFieldType()
  {
    if( null === $this->_fieldType ) {
      if( null === $this->_table ) {
        throw new Fields_Model_Exception('Unable to determine field type');
      } else {
        $this->_fieldType = $this->_table->getFieldType();
      }
    }

    return $this->_fieldType;
  }

  public function getFieldTableType()
  {
    if( null === $this->_fieldTableType ) {
      if( null === $this->_table ) {
        throw new Fields_Model_Exception('Unable to determine field table type');
      } else {
        $this->_fieldTableType = $this->_table->getFieldTableType();
      }
    }

    return $this->_fieldTableType;
  }
  
  public function __sleep()
  {
    $this->getFieldType(); // Make sure it gets populated
    $this->getFieldTableType(); // Make sure it gets populated
    
    $props = parent::__sleep();

    $props[] = '_fieldType';
    $props[] = '_fieldTableType';

    return $props;
  }

  public function __wakeup()
  {
    $fieldType = $this->getFieldType();
    $fieldTableType = $this->getFieldTableType();
    try {
      $table = Engine_Api::_()->fields()->getTable($fieldType, $fieldTableType);
      $this->_table = $table;
      $this->_connected = true;
    } catch( Exception $e ) {
      $this->_connected = false;
    }
  }
}