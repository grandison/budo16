<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Comments.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Comments extends Engine_Db_Table
{
  protected $_rowClass = 'Core_Model_Comment';

  protected $_custom = false;

  public function __construct($config = array())
  {
    if( get_class($this) !== 'Core_Model_DbTable_Comments' ) {
      $this->_custom = true;
    }

    parent::__construct($config);
  }

  public function getCommentTable()
  {
    return $this;
  }

  public function addComment(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster, $body)
  {
    $table = $this->getCommentTable();
    $row = $table->createRow();

    if( isset($row->resource_type) )
    {
      $row->resource_type = $resource->getType();
    }

    $row->resource_id = $resource->getIdentity();
    $row->poster_type = $poster->getType();
    $row->poster_id = $poster->getIdentity();

    $row->creation_date = date('Y-m-d H:i:s');
    $row->body = $body;
    $row->save();

    if( isset($resource->comment_count) )
    {
      $resource->comment_count++;
      $resource->save();
    }

    return $row;
  }

  public function removeComment(Core_Model_Item_Abstract $resource, $comment_id)
  {
    $row = $this->getComment($resource, $comment_id);
    if( null === $row )
    {
      throw new Core_Model_Exception('No comment found to delete');
    }
    
    $row->delete();

    if( isset($resource->comment_count) )
    {
      $resource->comment_count--;
      $resource->save();
    }

    return $this;
  }

  public function getComment(Core_Model_Item_Abstract $resource, $comment_id)
  {
    $table = $this->getCommentTable();
    $select = $table->select()
      ->where('comment_id = ?', $comment_id)
      ->limit(1);

    $comment = $table->fetchRow($select);

    /*
    if( !($comment instanceof Zend_Db_Table_Row_Abstract) || !isset($comment->comment_id) )
    {
      throw new Core_Model_Exception('Invalid argument or comment could not be found');
    }
     */

    return $comment;
  }

  public function getCommentSelect(Core_Model_Item_Abstract $resource)
  {
    $select = $this->getCommentTable()->select();

    if( !$this->_custom )
    {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select
      ->where('resource_id = ?', $resource->getIdentity())
      ; //->order('comment_id ASC');

    return $select;
  }

  public function getCommentPaginator(Core_Model_Item_Abstract $resource)
  {
    $paginator = Zend_Paginator::factory($this->getCommentSelect($resource));
    $paginator->setItemCountPerPage(3);
    $paginator->count();
    $pages = $paginator->getPageRange();
    $paginator->setCurrentPageNumber($pages);
    return $paginator;
  }

  public function getCommentCount(Core_Model_Item_Abstract $resource)
  {
    if( isset($resource->comment_count) )
    {
      return $resource->comment_count;
    }

    $select = new Zend_Db_Select($this->getCommentTable()->getAdapter());
    $select
      ->from($this->getCommentTable()->info('name'), new Zend_Db_Expr('COUNT(1) as count'));

    if( !$this->_custom )
    {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select->where('resource_id = ?', $resource->getIdentity());

    $data = $select->query()->fetchAll();
    return (int) $data[0]['count'];
  }

  public function getAllComments(Core_Model_Item_Abstract $resource)
  {
    return $this->getCommentTable()->fetchAll($this->getCommentSelect($resource));
  }

  public function getAllCommentsUsers(Core_Model_Item_Abstract $resource)
  {
    $table = $this->getCommentTable();
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