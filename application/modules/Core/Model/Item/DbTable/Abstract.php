<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Model_Item_DbTable_Abstract extends Engine_Db_Table
{
  protected $_itemType;

  protected $_localItemCache = array();
  
  public function __construct($config = array())
  {
    if( !isset($this->_rowClass) ) {
      $this->_rowClass = Engine_Api::_()->getItemClass($this->getItemType());
    }
    
    // @todo stuff
    parent::__construct($config);
  }

  public function getItemType()
  {
    if( null === $this->_itemType )
    {
      // Try to singularize item table class
      $segments = explode('_', get_class($this));
      $pluralType = array_pop($segments);
      $type = rtrim($pluralType, 's');
      if( !Engine_Api::_()->hasItemType($type) ) {
        $type = rtrim($pluralType, 'e');
        if( !Engine_Api::_()->hasItemType($type) ) {
          throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
        }
      }

      // Make sure we have a column matching
      $prop = $type . '_id';
      if( !in_array($prop, $this->info('cols')) )
      {
        throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
      }

      // Cool
      $this->_itemType = $type;
    }

    return $this->_itemType;
  }

  public function getItem($identity)
  {
    if( !array_key_exists((int) $identity, $this->_localItemCache) )
    {
      $this->_localItemCache[$identity] = $this->find($identity)->current();
    }

    return $this->_localItemCache[$identity];
  }

  public function getItemMulti(array $identities)
  {
    $todo = array();
    foreach( $identities as $identity )
    {
      if( !array_key_exists((int) $identity, $this->_localItemCache) )
      {
        $todo[] = $identity;
      }
    }

    if( count($todo) > 0 )
    {
      foreach( $this->find($todo) as $item )
      {
        $this->_localItemCache[$item->getIdentity()] = $item;
      }
    }

    $ret = array();
    foreach( $identities as $identity )
    {
      $ret[] = $this->_localItemCache[$identity];
    }

    return $ret;
  }
}