<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Forum.php 8240 2011-01-18 02:09:15Z shaun $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Model_Forum extends Core_Model_Item_Collectible
{
  protected $_children_types = array('forum_topic');

  protected $_parent_type = 'forum_category';

  protected $_owner_type = 'forum_category';

  protected $_collection_type = 'forum_category';

  protected $_collection_column_name = 'category_id';

  //We use membership system to manage moderators
  public function membership()
  {
    return new Engine_ProxyObject($this, $this->api()->getDbtable('membership', 'forum'));
  }  

  public function getCollection()
  {
    return Engine_Api::_()->getItem($this->_collection_type, $this->category_id);

  }

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'forum_forum',
      'reset' => true,
      'forum_id' => $this->getIdentity(),
      'slug' => $this->getSlug(),
      'action' => 'view',
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getSlug()
  {
    $translate = Zend_Registry::get('Zend_Translate');
    $title = $translate->translate($this->getTitle());
    return parent::getSlug($title);
  }

  public function getLastCreatedPost()
  {
    return Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
  }

  public function getLastUpdatedTopic()
  {
    $lastPost = Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
    if( !$lastPost ) return false;
    return Engine_Api::_()->getItem('forum_topic', $lastPost->topic_id);
    //return $this->getChildren('forum_topic', array('limit'=>1, 'order'=>'modified_date DESC'))->current();
  }

  // Hooks
  
  protected function _insert()
  {
    if( empty($this->category_id) ) {
      throw new Forum_Model_Exception('Cannot have a forum without a category');
    }

    // Increment parent forum count
    $category = $this->getParent();
    $category->forum_count = new Zend_Db_Expr('forum_count + 1');
    $category->save();

    parent::_insert();
  }

  protected function _update()
  {
    if( empty($this->category_id) ) {
      throw new Forum_Model_Exception('Cannot have a forum without a category');
    }

    parent::_update();
  }

  protected function _delete()
  {
    // Decrement parent forum count
    $category = $this->getParent();
    $category->forum_count = new Zend_Db_Expr('forum_count - 1');
    $category->save();

    // Delete all child topics
    $table = Engine_Api::_()->getItemTable('forum_topic');
    $select = $table->select()
      ->where('forum_id = ?', $this->getIdentity())
      ;
    foreach( $table->fetchAll($select) as $topic )
    {
      $topic->delete();
    }

    
    parent::_delete();
  }

  public function isModerator($user)
  {
    $list = $this->getModeratorList();
    return $list->has($user);
  }

  public function getModeratorList()
  {
    $table = Engine_Api::_()->getItemTable('forum_list');
    $select = $table->select()
      ->where('owner_id = ?', $this->getIdentity())
      ->limit(1);

    $list = $table->fetchRow($select);

    if( null === $list ) {
      $list = $table->createRow();
      $list->setFromArray(array(
        'owner_id' => $this->getIdentity(),
      ));
      $list->save();
    }

    return $list;
  }


  public function getPrevForum()
  {
    $table = $this->getTable();
    if( !in_array('order', $table->info('cols')) ) {
      throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
    }

    $select = $table->select()
      ->where('`order` < ?', $this->order)
      // Should be confined to a category
      ->where('`category_id` = ?', $this->category_id)
      ->order('order DESC')
      ->limit(1);
    
    return $table->fetchRow($select);
  }

  public function moveUp()
  {
    $table = $this->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $forums   = $table->select()->where('category_id = ?', $this->category_id)->order('order ASC')->query()->fetchAll();
      $newOrder = array();
      foreach ($forums as $forum) {
        if ($this->forum_id == $forum['forum_id']) {
          $prevForum = array_pop($newOrder);
          array_push($newOrder, $forum['forum_id']);
          if ($prevForum) {
            array_push($newOrder, $prevForum);
            unset($prevForum);
          }
        } else {
          array_push($newOrder, $forum['forum_id']);
        }
      }
      foreach ($table->fetchAll($table->select()) as $row) {
        if ($row->category_id == $this->category_id) {
          $order = array_search($row->forum_id, $newOrder);
          $row->order = $order+1;
          $row->save();
        }
      }
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollBack();
      throw $e;
    }
  }
}