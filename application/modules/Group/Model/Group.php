<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Group.php 7524 2010-10-01 22:42:11Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Model_Group extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';

  protected $_owner_type = 'user';

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'group_profile',
      'reset' => true,
      'id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getParent()
  {
    return $this->getOwner('user');
  }

  public function getSingletonAlbum()
  {
    $table = Engine_Api::_()->getItemTable('group_album');
    $select = $table->select()
      ->where('group_id = ?', $this->getIdentity())
      ->order('album_id ASC')
      ->limit(1);

    $album = $table->fetchRow($select);

    if( null === $album )
   {
      $album = $table->createRow();
      $album->setFromArray(array(
        'group_id' => $this->getIdentity()
      ));
      $album->save();
    }

    return $album;
  }

  public function getOfficerList()
  {
    $table = Engine_Api::_()->getItemTable('group_list');
    $select = $table->select()
      ->where('owner_id = ?', $this->getIdentity())
      ->where('title = ?', 'GROUP_OFFICERS')
      ->limit(1);

    $list = $table->fetchRow($select);

    if( null === $list ) {
      $list = $table->createRow();
      $list->setFromArray(array(
        'owner_id' => $this->getIdentity(),
        'title' => 'GROUP_OFFICERS',
      ));
      $list->save();
    }

    return $list;
  }

  public function getCategory()
  {
    return Engine_Api::_()->getDbtable('categories', 'group')->find($this->category_id)->current();
  }

  public function setPhoto($photo)
  {
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
    } else if( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
    } else if( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
    } else {
      throw new Group_Model_Exception('invalid argument passed to setPhoto');
    }

    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => 'group',
      'parent_id' => $this->getIdentity()
    );
    
    // Save
    $storage = Engine_Api::_()->storage();
    
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($path.'/m_'.$name)
      ->destroy();

    // Resize image (profile)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(200, 400)
      ->write($path.'/p_'.$name)
      ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(140, 160)
      ->write($path.'/in_'.$name)
      ->destroy();

    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($path.'/is_'.$name)
      ->destroy();

    // Store
    $iMain = $storage->create($path.'/m_'.$name, $params);
    $iProfile = $storage->create($path.'/p_'.$name, $params);
    $iIconNormal = $storage->create($path.'/in_'.$name, $params);
    $iSquare = $storage->create($path.'/is_'.$name, $params);

    $iMain->bridge($iProfile, 'thumb.profile');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');

    // Remove temp files
    @unlink($path.'/p_'.$name);
    @unlink($path.'/m_'.$name);
    @unlink($path.'/in_'.$name);
    @unlink($path.'/is_'.$name);

    // Update row
    $this->modified_date = date('Y-m-d H:i:s');
    $this->photo_id = $iMain->file_id;
    $this->save();

    // Add to album
    $viewer = Engine_Api::_()->user()->getViewer();
    $photoTable = Engine_Api::_()->getItemTable('group_photo');
    $groupAlbum = $this->getSingletonAlbum();
    $photoItem = $photoTable->createRow();
    $photoItem->setFromArray(array(
      'group_id' => $this->getIdentity(),
      'album_id' => $groupAlbum->getIdentity(),
      'user_id' => $viewer->getIdentity(),
      'file_id' => $iMain->getIdentity(),
      'collection_id' => $groupAlbum->getIdentity(),
    ));
    $photoItem->save();

    return $this;
  }

  public function getEventsPaginator()
  {

    $table = Engine_Api::_()->getDbtable('events', 'event');
    $select = $table->select()->where('parent_type = ?', 'group');
    $select = $select->where('parent_id = ?', $this->getIdentity());
    return  Zend_Paginator::factory($select);

  }

  public function membership()
  {
    return new Engine_ProxyObject($this, $this->api()->getDbtable('membership', 'group'));
  }


  // Internal hooks

  protected function _postInsert()
  {
    if( $this->_disableHooks ) return;
    
    parent::_postInsert();
    
    // Create auth stuff
    $context = $this->api()->authorization()->context;
    foreach( array('everyone', 'registered', 'member') as $role )
    {
      $context->setAllowed($this, $role, 'view', true);
    }
  }

  protected function _delete()
  {
    if( $this->_disableHooks ) return;

    // Delete all memberships
    $this->membership()->removeAllMembers();

    // Delete officer list
    $this->getOfficerList()->delete();

    // Delete all albums
    $albumTable = Engine_Api::_()->getItemTable('group_album');
    $albumSelect = $albumTable->select()->where('group_id = ?', $this->getIdentity());
    foreach( $albumTable->fetchAll($albumSelect) as $groupAlbum ) {
      $groupAlbum->delete();
    }

    // Delete all topics
    $topicTable = Engine_Api::_()->getItemTable('group_topic');
    $topicSelect = $topicTable->select()->where('group_id = ?', $this->getIdentity());
    foreach( $topicTable->fetchAll($topicSelect) as $groupTopic ) {
      $groupTopic->delete();
    }

    if (Engine_Api::_()->hasItemType('event'))
    {  
      $eventTable = Engine_Api::_()->getItemTable('event');
      $eventSelect = $eventTable->select()->where("parent_type = 'group' and parent_id = ?", $this->getIdentity());
      foreach ($eventTable->fetchAll($eventSelect) as $groupEvent)
      {
        $groupEvent->delete();
      }
    }
    parent::_delete();
  }
}