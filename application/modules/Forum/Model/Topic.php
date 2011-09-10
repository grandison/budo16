<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Topic.php 7833 2010-11-19 03:28:18Z steve $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Model_Topic extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'forum_forum';
  
  protected $_owner_type = 'user';

  protected $_children_types = array('forum_post');


  // Generic content methods

  public function getDescription()
  {
    if( !isset($this->store()->firstPost) ) {
      $postTable = Engine_Api::_()->getDbtable('posts', 'forum');
      $postSelect = $postTable->select()
        ->where('topic_id = ?', $this->getIdentity())
        ->order('post_id ASC')
        ->limit(1);
      $this->store()->firstPost = $postTable->fetchRow($postSelect);
    }
    if( isset($this->store()->firstPost) ) {
      return strip_tags($this->store()->firstPost->body);
    }
    return '';
  }

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'forum_topic',
      'reset' => true,
      'topic_id' => $this->getIdentity(),
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
  

  // hooks

  protected function _insert()
  {
    if( empty($this->forum_id) ) {
      throw new Forum_Model_Exception('Cannot have a topic without a forum');
    }

    if( empty($this->user_id) ) {
      throw new Forum_Model_Exception('Cannot have a topic without a user');
    }

    // Increment parent topic count
    $forum = $this->getParent();
    $forum->topic_count = new Zend_Db_Expr('topic_count + 1');
    $forum->modified_date = date('Y-m-d H:i:s');
    $forum->save();

    parent::_insert();
  }

  protected function _update()
  {
    if( empty($this->forum_id) ) {
      throw new Forum_Model_Exception('Cannot have a topic without a forum');
    }

    if( empty($this->user_id) ) {
      throw new Forum_Model_Exception('Cannot have a topic without a user');
    }

    if( !empty($this->_modifiedFields['forum_id']) ) {
      $originalForumIdentity = $this->getTable()->select()
        ->from($this->getTable()->info('name'), 'forum_id')
        ->where('topic_id = ?', $this->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( $originalForumIdentity != $this->forum_id ) {
        $postsTable = Engine_Api::_()->getItemTable('forum_post');

        $topicLastPost = $this->getLastCreatedPost();

        $oldForum = Engine_Api::_()->getItem('forum', $originalForumIdentity);
        $newForum = Engine_Api::_()->getItem('forum', $this->forum_id);

        $oldForumLastPost = $oldForum->getLastCreatedPost();
        $newForumLastPost = $newForum->getLastCreatedPost();

        // Update old forum
        $oldForum->topic_count = new Zend_Db_Expr('topic_count - 1');
        $oldForum->post_count = new Zend_Db_Expr(sprintf('post_count - %d', $this->post_count));
        if( !$oldForumLastPost || $oldForumLastPost->topic_id == $this->getIdentity() ) {
          // Update old forum last post
          $oldForumNewLastPost = $postsTable->select()
            ->from($postsTable->info('name'), array('post_id', 'user_id'))
            ->where('forum_id = ?', $originalForumIdentity)
            ->where('topic_id != ?', $this->getIdentity())
            ->order('post_id DESC')
            ->limit(1)
            ->query()
            ->fetch();
          if( $oldForumNewLastPost ) {
            $oldForum->lastpost_id = $oldForumNewLastPost['post_id'];
            $oldForum->lastposter_id = $oldForumNewLastPost['user_id'];
          } else {
            $oldForum->lastpost_id = 0;
            $oldForum->lastposter_id = 0;
          }
        }
        $oldForum->save();

        // Update new forum
        $newForum->topic_count = new Zend_Db_Expr('topic_count + 1');
        $newForum->post_count = new Zend_Db_Expr(sprintf('post_count + %d', $this->post_count));
        if( !$newForumLastPost || strtotime($topicLastPost->creation_date) > strtotime($newForumLastPost->creation_date) ) {
          // Update new forum last post
          $newForum->lastpost_id = $topicLastPost->post_id;
          $newForum->lastposter_id = $topicLastPost->user_id;
        }
        if( strtotime($topicLastPost->creation_date) > strtotime($newForum->modified_date) ) {
          $newForum->modified_date = $topicLastPost->creation_date;
        }
        $newForum->save();
        
        // Update posts
        $postsTable = Engine_Api::_()->getItemTable('forum_post');
        $postsTable->update(array(
          'forum_id' => $this->forum_id,
        ), array(
          'topic_id = ?' => $this->getIdentity(),
        ));
      }
    }

    parent::_update();
  }

  protected function _delete()
  {
    $forum = $this->getParent();
    
    // Decrement forum topic and post count
    $forum->topic_count = new Zend_Db_Expr('topic_count - 1');
    $forum->post_count = new Zend_Db_Expr(sprintf('post_count - %s', $this->post_count));

    // Update forum last post
    $olderForumLastPost = Engine_Api::_()->getDbtable('posts', 'forum')->select()
      ->where('forum_id = ?', $this->forum_id)
      ->where('topic_id != ?', $this->topic_id)
      ->order('post_id DESC')
      ->limit(1)
      ->query()
      ->fetch();

    if( $olderForumLastPost['post_id'] != $forum->lastpost_id ) {
      if( $olderForumLastPost ) {
        $forum->lastpost_id = $olderForumLastPost['post_id'];
        $forum->lastposter_id = $olderForumLastPost['user_id'];
      } else {
        $forum->lastpost_id = null;
        $forum->lastposter_id = null;
      }
    }

    $forum->save();

    // Delete all posts
    $table = Engine_Api::_()->getItemTable('forum_post');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ;

    foreach( $table->fetchAll($select) as $post ) {
      $post->deletingTopic = true;
      $post->delete();
    }

    // remove topic views
    Engine_Api::_()->getDbTable('topicviews', 'forum')->delete(array(
      'topic_id = ?' => $this->topic_id,
    ));

    // remove topic watches
    Engine_Api::_()->getDbTable('topicWatches', 'forum')->delete(array(
      'resource_id = ?' => $this->forum_id,
      'topic_id = ?' => $this->topic_id,
    ));
    
    parent::_delete();
  }

  public function getLastCreatedPost()
  {
    $post = Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
    if (!$post) {
      // this can happen if the last post was deleted
      $table  = Engine_Api::_()->getDbTable('posts', 'forum');
      $post   = $table->fetchRow(array('topic_id = ?' => $this->getIdentity()), 'creation_date DESC');
      if ($post) {
        // update topic table with valid information
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
          $row = Engine_Api::_()->getItem('forum_topic', $this->getIdentity());
          $row->lastpost_id   = $post->getIdentity();
          $row->lastposter_id = $post->getOwner('user')->getIdentity();
          $row->save();
          $db->commit();
        } catch (Exception $e) {
          $db->rollback();
          // @todo silence error?
        }        
      }
    }
    return $post;
  }

  public function registerView($user)
  {
    $table = Engine_Api::_()->getDbTable('topicviews', 'forum');
    $table->delete(array('topic_id = ?'=>$this->getIdentity(), 'user_id = ?'=>$user->getIdentity()));
    $row = $table->createRow();
    $row->user_id = $user->user_id;
    $row->topic_id = $this->topic_id;
    $row->last_view_date = date('Y-m-d H:i:s');
    $row->save();
  }

  public function isViewed($user)
  {
    $table = Engine_Api::_()->getDbTable('topicviews', 'forum');
    $row = $table->fetchRow($table->select()->where('user_id = ?', $user->getIdentity())->where('last_view_date > ?', $this->modified_date)->where("topic_id = ?", $this->getIdentity()));
    return $row != null;
  }

  public function getLastPage($per_page)
  {
    return ceil($this->post_count / $per_page);
  }

  public function getAuthorizationItem()
  {
    return $this->getParent();
  }
}