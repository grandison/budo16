<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7337 2010-09-10 00:07:38Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Api_Core extends Core_Api_Abstract
{
  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;

  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;
  
  public function getGroupSelect($params = array())
  {
    $table = Engine_Api::_()->getItemTable('group');
    $select = $table->select();
    // Search
    if( isset($params['search']) )
    {
      $select->where('search = ?', (bool) $params['search']);
    }
    // User-based
    if( !empty($params['owner']) && $params['owner'] instanceof Core_Model_Item_Abstract )
    {
      $select->where('user_id = ?', $params['owner']->getIdentity());
    }
    else if( !empty($params['user_id']) )
    {
      $select->where('user_id = ?', $params['user_id']);
    }
    else if( !empty($params['users']) && is_array($params['users']) )
    {
      foreach( $params['users'] as &$id ) if( !is_numeric($id) ) $id = 0;
      $params['users'] = array_filter($params['users']);
      $select->where('user_id IN(\''.join("', '", $params['users']).'\')');
    }
    // Category
    if( !empty($params['category_id']) )
    {
      $select->where('category_id = ?', $params['category_id']);
    }
    // Order
    if( !empty($params['order']) )
    {
      $select->order($params['order']);
    }
    else {
      $select->order('creation_date DESC');
    }
    return $select;
  }

  public function getGroupPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getGroupSelect($params));
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
      $photo_params = array(
        'parent_id' => $params['group_id'],
        'parent_type' => 'group',
      );

      $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
      $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
      $photoFile->bridge($thumbFile, 'thumb.normal');

      $params['file_id'] = $photoFile->file_id; // This might be wrong
      $params['photo_id'] = $photoFile->file_id;
      $params['owner_id'] = $photoFile->parent_id;
      $params['owner_type'] = $photoFile->parent_type;
      unset($params['owner_id']);
      unset($params['owner_type']);

      // Remove temp files
      @unlink($mainName);
      @unlink($thumbName);
    }

    $row = Engine_Api::_()->getDbtable('photos', 'group')->createRow();
    $row->setFromArray($params);
    $row->save();
    return $row;
  }

  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'group');
    return $table->fetchAll($table->select()->order('title ASC'));
  }

  public function getCategory($category_id)
  {
    $table = $this->api()->getDbtable('categories', 'group');
    $row = $table->fetchRow($table->select()->where('category_id = ?', $category_id));
    return $row;
  }


}