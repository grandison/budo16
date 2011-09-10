<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8447 2011-02-12 00:37:26Z jung $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Sami
 */
class Event_Api_Core extends Core_Api_Abstract
{

  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;

  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;


 public function getEventSelect($params = array())
  {
    $table = Engine_Api::_()->getItemTable('event');
    $select = $table->select();
    if( isset($params['search']) )
    {
      $select->where('search = ?', (bool) $params['search']);
    }
    if( isset($params['owner']) && $params['owner'] instanceof Core_Model_Item_Abstract )
    {
      $select->where('user_id = ?', $params['owner']->getIdentity());
    }
    else if( isset($params['user_id']) && !empty($params['user_id']) )
    {
      $select->where('user_id = ?', $params['user_id']);
    }
    else if( isset($params['users']) && is_array($params['users']) )
    {
      $users = array();
      foreach ($params['users'] as $user_id) {
        if (is_int($user_id) && $user_id > 0) {
          $users[] = $user_id;
        }
      }
      // if users is set yet there are none, $select will always return an empty rowset
      if (empty($users))
        return $select->where('1 != 1');
      else
        $select->where("user_id IN (?)", $users);
    }
    // Category
    if( isset($params['category_id']) && !empty($params['category_id']) )
    {
      $select->where('category_id = ?', $params['category_id']);
    }
    // Endtime
    if( isset($params['past']) && !empty($params['past']) )
    {
      $select->where("endtime <= FROM_UNIXTIME(?)", time());
    }
    elseif( isset($params['future']) && !empty($params['future']) ) {
      $select->where("endtime > FROM_UNIXTIME(?)", time());
    }
    // Order
    if( isset($params['order']) && !empty($params['order']) )
    {
      $select->order($params['order']);
    }
    else {
      $select->order('starttime');
    }
    return $select;
  }

  public function getEventPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getEventSelect($params));
  }



  public function createPhoto($params, $file)
  {
    if( $file instanceof Storage_Model_File )
    {
      $params['file_id'] = $file->getIdentity();
    }

    else
    {
      // Get image info and resize
      $name = basename($file['tmp_name']);
      $path = dirname($file['tmp_name']);
      $extension = ltrim(strrchr($file['name'], '.'), '.');

      $mainName = $path.'/m_'.$name . '.' . $extension;
      $thumbName = $path.'/t_'.$name . '.' . $extension;

      $image = Engine_Image::factory();
      $image->open($file['tmp_name'])
          ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
          ->write($mainName)
          ->destroy();

      $image = Engine_Image::factory();
      $image->open($file['tmp_name'])
          ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
          ->write($thumbName)
          ->destroy();

      // Store photos
      $photo_params = Array('parent_id'=>$params['user_id'], 'parent_type'=>'event');

      $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
      $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
      $photoFile->bridge($thumbFile, 'thumb.normal');

      $params['file_id'] = $photoFile->file_id; // This might be wrong
      $params['photo_id'] = $photoFile->file_id;

      // Remove temp files
      @unlink($mainName);
      @unlink($thumbName);
    }
    unset($photo_params['owner_id']);
    unset($photo_params['owner_type']);
    $photo_params['user_id'] = $params['user_id'];
    $row = Engine_Api::_()->getDbtable('photos', 'event')->createRow();
    //$row->owner_id = $params['parent_id'];
    //$row->owner_type = $params['parent_type'];
    unset($params['parent_type']);
    unset($params['parent_id']);
    $row->setFromArray($params);
    $row->save();
    return $row;

  }

  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'event');
    return $table->fetchAll($table->select()->order('title ASC'));
  }

  public function getCategory($category_id)
  {
    $table = $this->api()->getDbtable('categories', 'event');
    $row = $table->fetchRow($table->select()->where('category_id = ?', $category_id));
    return $row;
  }

}