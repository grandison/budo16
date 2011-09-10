<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Collectible.php 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Model_Item_Collectible extends Core_Model_Item_Abstract 
{
  protected $_collection_type;
  protected $_collection_column_name = 'category_id';

  /**
   * This
   *
  public function isOwner(Core_Model_Item_Abstract $owner)
  {
    return $this->getCollection()->isOwner($owner);
  }
  
  public function getOwner()
  {
    if( !isset($this->owner_id) )
    {
      return $this->getCollection()->getOwner();
    }
    else
    {
      return parent::getOwner();
    }
  }
   *
   */

  
  public function getCollectionIndex()
  {
    return $this->getCollection()->getCollectibleIndex($this);
  }

  public function getNextCollectible()
  {
    return $this->getCollection()->getNextCollectible($this);
  }

  public function getPrevCollectible()
  {
    return $this->getCollection()->getPrevCollectible($this);
  }

  public function moveUp()
  {
    $table = $this->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try 
    { 
      $last = $this->getPrevCollectible();
      $temp = $this->order;
      $this->order = $last->order;
      $last->order = $temp;
      $this->save();
      $last->save();
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollBack();
      throw $e;
    }
  }

  
  public function getCollection()
  {
    if( !isset($this->collection_id) )
    {
      throw new Core_Model_Item_Exception('If column with collection_id not defined, must override getCollection()');
    }

    return Engine_Api::_()->getItem($this->_collection_type, $this->collection_id);
  }


  // Internal hook

  protected function _insert()
  {
    $collection = $this->getCollection();
    if( $collection && isset($collection->collectible_count) )
    {
      $collection->collectible_count++;
      $collection->save();
    }
    parent::_insert();
  }

  protected function _delete()
  {
    // @todo problems may occur if this is getting deleted with parent
    $collection = $this->getCollection();
    if( $collection && isset($collection->collectible_count) )
    {
      $collection->collectible_count--;
      $collection->save();
    }
    
    parent::_delete();
  }
}