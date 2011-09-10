<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8301 2011-01-25 07:54:45Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Api_Core extends Core_Api_Abstract
{
  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;
  
  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;

  protected $_collectible_type = "photo";

  public function createAlbum($params)
  {
    return $this->_createItem("album", $params);
  }


  public function createPhoto($params, $file)
  {
    // Create album photo
    $albumPhoto = Engine_Api::_()->getDbtable('photos', 'album')->createRow();
    $albumPhoto->setFromArray($params);
    $albumPhoto->save();

    // Set photo
    $albumPhoto->setPhoto($file);
    
    return $albumPhoto;
  }

  public function getUserAlbums($user)
  {
    $table = Engine_Api::_()->getItemTable('album');
    return $table->fetchAll($table->select()->where("owner_type = ?", "user")->where("owner_id = ?", $user->user_id));
  }


  public function getAlbumSelect($options = array())
  {
    $table = Engine_Api::_()->getItemTable('album');
    $select = $table->select();
    if( !empty($options['owner']) && $options['owner'] instanceof Core_Model_Item_Abstract )
    {
      $select
        ->where('owner_type = ?', $options['owner']->getType())
        ->where('owner_id = ?', $options['owner']->getIdentity())
        ->order('modified_date DESC')
        ;
    }

    if( !empty($options['search']) && is_numeric($options['search']) )
    {
      $select->where('search = ?', $options['search']);
    }

    return $select;
  }

  public function getAlbumPaginator($options = array())
  {
    return Zend_Paginator::factory($this->getAlbumSelect($options));
  }

  /**
   * Returns a collection of all the categories in the album plugin
   *
   * @return Zend_Db_Table_Select
   */
  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'album');
    return $table->fetchAll($table->select()->order('category_name ASC'));
  }

  /**
   * Returns a category item
   *
   * @param Int category_id
   * @return Zend_Db_Table_Select
   */
  public function getCategory($category_id)
  {
    return Engine_Api::_()->getDbtable('categories', 'album')->find($category_id)->current();
  }
}
