<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Post.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Model_Post extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'event_topic';

  protected $_owner_type = 'user';
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'event_extended',
      'controller' => 'topic',
      'action' => 'view',
      'event_id' => $this->event_id,
      'topic_id' => $this->getParentTopic()->getIdentity(),
      'post_id' => $this->getIdentity(),
    ), $params);
    $route = @$params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getDescription()
  {
    return Engine_String::substr($this->body, 0, 255);
  }

  public function getPostIndex()
  {
    $table = $this->getTable();
    $select = new Zend_Db_Select($table->getAdapter());
    $select
      ->from($table->info('name'), new Zend_Db_Expr('COUNT(post_id) as count'))
      ->where('topic_id = ?', $this->topic_id)
      ->where('post_id < ?', $this->getIdentity())
      ->order('post_id ASC')
      ;

    $data = $select->query()->fetch();
    
    return (int) $data['count'];
  }

  public function getParentEvent()
  {
    return Engine_Api::_()->getItem('event', $this->event_id);
  }

  public function getParentTopic()
  {
    return Engine_Api::_()->getItem('event_topic', $this->topic_id);
  }

  public function getPoster()
  {
    return Engine_Api::_()->getItem('user', $this->user_id);
  }

  public function getAuthorizationItem()
  {
    return $this->getParent('event');
  }



  // Internal hooks

  protected function _insert()
  {
    if( !$this->event_id )
    {
      throw new Exception('Cannot create post without event_id');
    }
    
    if( !$this->topic_id )
    {
      throw new Exception('Cannot create post without topic_id');
    }

    // Update topic
    $table = Engine_Api::_()->getDbtable('topics', 'event');
    $select = $table->select()->where('topic_id = ?', $this->topic_id)->limit(1);
    $topic = $table->fetchRow($select);

    $topic->lastpost_id = $this->post_id;
    $topic->lastposter_id = $this->user_id;
    $topic->modified_date = date('Y-m-d H:i:s');
    $topic->post_count++;
    $topic->save();
    
    parent::_insert();
  }

  protected function _delete()
  {
    // Update topic
    $table = Engine_Api::_()->getDbtable('topics', 'event');
    $select = $table->select()->where('topic_id = ?', $this->topic_id)->limit(1);
    $topic = $table->fetchRow($select);
    $topic->post_count--;

    if( $topic->post_count == 0 ) {
      $topic->delete();
    } else {
      $topic->save();
    }
  }

  public function canEdit($user)
  {
    return $this->getParent()->getParent()->authorization()->isAllowed($user, 'edit') || $this->isOwner($user);
  }


}