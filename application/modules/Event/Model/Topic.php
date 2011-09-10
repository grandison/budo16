<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Topic.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Model_Topic extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'event';

  protected $_owner_type = 'user';

  protected $_children_types = array('event_post');
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'event_extended',
      'controller' => 'topic',
      'action' => 'view',
      'event_id' => $this->event_id,
      'topic_id' => $this->getIdentity(),
    ), $params);
    $route = @$params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getDescription()
  {
    $firstPost = $this->getFirstPost();
    return ( null !== $firstPost ? Engine_String::substr($firstPost->body, 0, 255) : '' );
  }
  
  public function getParentEvent()
  {
    return Engine_Api::_()->getItem('event', $this->event_id);
  }

  public function getFirstPost()
  {
    $table = Engine_Api::_()->getDbtable('posts', 'event');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ->order('post_id ASC')
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPost()
  {
    $table = Engine_Api::_()->getItemTable('event_post');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ->order('post_id DESC')
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPoster()
  {
    return Engine_Api::_()->getItem('user', $this->lastposter_id);
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
      throw new Exception('Cannot create topic without event_id');
    }

    /*
    $this->getParentEvent()->setFromArray(array(

    ))->save();
    */

    parent::_insert();
  }
}