<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Likes.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Likes extends Engine_Db_Table
{
  protected $_rowClass = 'Core_Model_Like';

  protected $_custom = false;

  public function __construct($config = array())
  {
    if( get_class($this) !== 'Core_Model_DbTable_Likes' ) {
      $this->_custom = true;
    }

    parent::__construct($config);
  }

  public function getLikeTable()
  {
    return $this;
  }

  public function addLike(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster)
  {
    $row = $this->getLike($resource, $poster);
    if( null !== $row )
    {
      throw new Core_Model_Exception('Already liked');
    }

    $table = $this->getLikeTable();
    $row = $table->createRow();

    if( isset($row->resource_type) )
    {
      $row->resource_type = $resource->getType();
    }

    $row->resource_id = $resource->getIdentity();
    $row->poster_type = $poster->getType();
    $row->poster_id = $poster->getIdentity();
    $row->save();

    if( isset($resource->like_count) )
    {
      $resource->like_count++;
      $resource->save();
    }

    return $row;
  }

  public function removeLike(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster)
  {
    $row = $this->getLike($resource, $poster);
    if( null === $row )
    {
      throw new Core_Model_Exception('No like to remove');
    }

    $row->delete();

    if( isset($resource->like_count) )
    {
      $resource->like_count--;
      $resource->save();
    }

    return $this;
  }

  public function isLike(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster)
  {
    return ( null !== $this->getLike($resource, $poster) );
  }

  public function getLike(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster)
  {
    $table = $this->getLikeTable();
    $select = $this->getLikeSelect($resource)
      ->where('poster_type = ?', $poster->getType())
      ->where('poster_id = ?', $poster->getIdentity())
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLikeSelect(Core_Model_Item_Abstract $resource)
  {
    $select = $this->getLikeTable()->select();

    if( !$this->_custom )
    {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select
      ->where('resource_id = ?', $resource->getIdentity())
      ->order('like_id ASC');

    return $select;
  }

  public function getLikePaginator(Core_Model_Item_Abstract $resource)
  {
    $paginator = Zend_Paginator::factory($this->getLikeSelect($resource));
    $paginator->setItemCountPerPage(3);
    $paginator->count();
    $pages = $paginator->getPageRange();
    $paginator->setCurrentPageNumber($pages);
    return $paginator;
  }

  public function getLikeCount(Core_Model_Item_Abstract $resource)
  {
    if( isset($resource->like_count) )
    {
      return $resource->like_count;
    }

    $select = new Zend_Db_Select($this->getLikeTable()->getAdapter());
    $select
      ->from($this->getLikeTable()->info('name'), new Zend_Db_Expr('COUNT(1) as count'));

    if( !$this->_custom )
    {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select->where('resource_id = ?', $resource->getIdentity());

    $data = $select->query()->fetchAll();
    return (int) $data[0]['count'];
  }

  public function getAllLikes(Core_Model_Item_Abstract $resource)
  {
    return $this->getLikeTable()->fetchAll($this->getLikeSelect($resource));
  }

  public function getAllLikesUsers(Core_Model_Item_Abstract $resource)
  {
    $table = $this->getLikeTable();
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), array('poster_type', 'poster_id'));

    if( !$this->_custom )
    {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select->where('resource_id = ?', $resource->getIdentity());

    $users = array();
    foreach( $select->query()->fetchAll() as $data )
    {
      if( $data['poster_type'] == 'user' )
      {
        $users[] = $data['poster_id'];
      }
    }
    $users = array_values(array_unique($users));

    return Engine_Api::_()->getItemMulti('user', $users);
  }
}