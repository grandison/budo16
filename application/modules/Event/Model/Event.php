<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Event.php 7735 2010-11-03 02:41:45Z jung $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Model_Event extends Core_Model_Item_Abstract
{

  protected $_owner_type = 'user';

  public function membership()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('membership', 'event'));
  }

  public function _postInsert()
  {
    parent::_postInsert();
    // Create auth stuff
    $context = $this->api()->authorization()->context;
    $context->setAllowed($this, 'everyone', 'view', true);
    $context->setAllowed($this, 'registered', 'comment', true);
    $viewer = Engine_Api::_()->user()->getViewer();
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
      throw new Event_Model_Exception('invalid argument passed to setPhoto');
    }

    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
     'parent_id' => $this->getIdentity(),
     'parent_type'=>'event'
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
    $photoTable = Engine_Api::_()->getItemTable('event_photo');
    $eventAlbum = $this->getSingletonAlbum();
    $photoItem = $photoTable->createRow();
    $photoItem->setFromArray(array(
      'event_id' => $this->getIdentity(),
      'album_id' => $eventAlbum->getIdentity(),
      'user_id' => $viewer->getIdentity(),
      'file_id' => $iMain->getIdentity(),
      'collection_id' => $eventAlbum->getIdentity(),
      'user_id' =>$viewer->getIdentity(),
    ));
    $photoItem->save();

    return $this;
  }

  public function getDescription()
  {
    // @todo decide how we want to handle multibyte string functions
    $tmpBody = strip_tags($this->description);
    return ( Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody );
  }

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'event_profile',
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

  protected function _delete()
  {
    if( $this->_disableHooks ) return;

    // Delete all memberships
    $this->membership()->removeAllMembers();


    // Delete all albums
    $albumTable = Engine_Api::_()->getItemTable('event_album');
    $albumSelect = $albumTable->select()->where('event_id = ?', $this->getIdentity());
    foreach( $albumTable->fetchAll($albumSelect) as $eventAlbum ) {
      $eventAlbum->delete();
    }

    // Delete all topics
    $topicTable = Engine_Api::_()->getItemTable('event_topic');
    $topicSelect = $topicTable->select()->where('event_id = ?', $this->getIdentity());
    foreach( $topicTable->fetchAll($topicSelect) as $eventTopic ) {
      $eventTopic->delete();
    }
    
    parent::_delete();
  }


  public function getSingletonAlbum()
  {
    $table = Engine_Api::_()->getItemTable('event_album');
    $select = $table->select()
      ->where('event_id = ?', $this->getIdentity())
      ->order('album_id ASC')
      ->limit(1);

    $album = $table->fetchRow($select);

    if( null === $album )
   {
      $album = $table->createRow();
      $album->setFromArray(array(
        'event_id' => $this->getIdentity()
      ));
      $album->save();
    }

    return $album;
  }

  public function categoryName()
  {
    $categories = Engine_Api::_()->event()->getCategories();
    return $categories[$this->category];
  }

  public function getAttendingCount()
  {
    return $this->membership()->getMemberCount(true, Array('rsvp' => 2));
  }

  public function getMaybeCount()
  {
    return $this->membership()->getMemberCount(true, Array('rsvp' => 1));
  }

  public function getNotAttendingCount()
  {
    return $this->membership()->getMemberCount(true, Array('rsvp' => 0));
  }

  public function getAwaitingReplyCount()
  {
    return $this->membership()->getMemberCount(false, Array('rsvp' => 3));
  }
}