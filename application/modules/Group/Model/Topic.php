<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Topic.php 7933 2010-12-05 02:25:06Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Model_Topic extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'group';

  protected $_owner_type = 'user';

  protected $_children_types = array('group_post');
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'group_extended',
      'controller' => 'topic',
      'action' => 'view',
      'group_id' => $this->group_id,
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
  
  public function getParentGroup()
  {
    return Engine_Api::_()->getItem('group', $this->group_id);
  }

  public function getFirstPost()
  {
    $table = Engine_Api::_()->getDbtable('posts', 'group');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ->order('post_id ASC')
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPost()
  {
    $table = Engine_Api::_()->getItemTable('group_post');
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
    return $this->getParent('group');
  }



  // Internal hooks

  protected function _insert()
  {
    if( $this->_disableHooks ) return;
    
    if( !$this->group_id )
    {
      throw new Exception('Cannot create topic without group_id');
    }

    /*
    $this->getParentGroup()->setFromArray(array(

    ))->save();
    */

    parent::_insert();
  }

  protected function _delete()
  {
    if( $this->_disableHooks ) return;
    
    // Delete all child posts
    $postTable = Engine_Api::_()->getItemTable('group_post');
    $postSelect = $postTable->select()->where('topic_id = ?', $this->getIdentity());
    foreach( $postTable->fetchAll($postSelect) as $groupPost ) {
      $groupPost->disableHooks()->delete();
    }
    
    parent::_delete();
  }

  public function canEdit($user)
  {
    return $this->getParent()->authorization()->isAllowed($user, 'edit') || $this->getParent()->authorization()->isAllowed($user, 'topic.edit') || $this->isOwner($user);
  }
}