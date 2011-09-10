<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8028 2010-12-10 19:11:19Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developmentsedsafd
 * @license    http://www.socialengine.net/license/
 */
class Classified_Api_Core extends Core_Api_Abstract
{
  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;

  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;
  // Select
  /**
   * Gets a paginator for classifieds
   *
   * @param Core_Model_Item_Abstract $user The user to get the messages for
   * @return Zend_Paginator
   */
  public function getClassifiedsPaginator($params = array(), $customParams = null)
  {
    $paginator = Zend_Paginator::factory($this->getClassifiedsSelect($params, $customParams));
    if( !empty($params['page']) )
    {
      $paginator->setCurrentPageNumber($params['page']);
    }
    if( !empty($params['limit']) )
    {
      $paginator->setItemCountPerPage($params['limit']);
    }
    return $paginator;
  }

  /**
   * Gets a select object for the user's classified entries
   *
   * @param Core_Model_Item_Abstract $user The user to get the messages for
   * @return Zend_Db_Table_Select
   */
  public function getClassifiedsSelect($params = array(), $customParams = null)
  {
    $table = Engine_Api::_()->getDbtable('classifieds', 'classified');
    $rName = $table->info('name');

    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');

    $searchTable = Engine_Api::_()->fields()->getTable('classified', 'search')->info('name');

    $select = $table->select()
      ->order( !empty($params['orderby']) ? $rName.'.'.$params['orderby'].' DESC' : $rName.'.creation_date DESC' );
    //die(print_r($customParams));

    if(isset($customParams)){
      $select = $select
        ->setIntegrityCheck(false)
        ->from($rName)
        ->joinLeft($searchTable, "$searchTable.item_id = $rName.classified_id");
        //->group("$rName.classified_id");

      $searchParts = Engine_Api::_()->fields()->getSearchQuery('classified', $customParams);
      foreach( $searchParts as $k => $v ) {
        $select->where("`{$searchTable}`.{$k}", $v);
      }     
    }

    if( !empty($params['user_id']) && is_numeric($params['user_id']) )
    {
      $select->where($rName.'.owner_id = ?', $params['user_id']);
    }

    if( !empty($params['user']) && $params['user'] instanceof User_Model_User )
    {
      $select->where($rName.'.owner_id = ?', $params['user_id']->getIdentity());
    }

    if( !empty($params['users']) )
    {
      $str = (string) ( is_array($params['users']) ? "'" . join("', '", $params['users']) . "'" : $params['users'] );
      $select->where($rName.'.owner_id in (?)', new Zend_Db_Expr($str));
    }

    if( !empty($params['tag']) )
    {
      $select
        ->setIntegrityCheck(false)
        ->joinLeft($tmName, "$tmName.resource_id = $rName.classified_id")
        ->where($tmName.'.resource_type = ?', 'classified')
        ->where($tmName.'.tag_id = ?', $params['tag']);
    }

    if( !empty($params['category']) )
    {
      $select->where($rName.'.category_id = ?', $params['category']);
    }

    if( isset($params['closed']) && $params['closed']!="" )
    {
      $select->where($rName.'.closed = ?', $params['closed']);
    }

    // Could we use the search indexer for this?
    if( !empty($params['search']) )
    {
      $select->where($rName.".title LIKE ? OR ".$rName.".body LIKE ?", '%'.$params['search'].'%');
    }

    if( !empty($params['start_date']) )
    {
      $select->where($rName.".creation_date > ?", date('Y-m-d', $params['start_date']));
    }

    if( !empty($params['end_date']) )
    {
      $select->where($rName.".creation_date < ?", date('Y-m-d', $params['end_date']));
    }

    if( !empty($params['has_photo']) ) {
      $select->where($rName.".photo_id > ?", 0);
    }

   //die($params['closed'].$select);
    return $select;
  }


  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'classified');
    return $table->fetchAll($table->select()->order('category_name ASC'));
  }

  public function getCategory($category_id)
  {
    return Engine_Api::_()->getDbtable('categories', 'classified')->find($category_id)->current();
  }

  public function getUserCategories($user_id)
  {
    $table  = Engine_Api::_()->getDbtable('categories', 'classified');
    $uName = Engine_Api::_()->getDbtable('classifieds', 'classified')->info('name');
    $iName = $table->info('name');

    $select = $table->select()
      ->setIntegrityCheck(false)
      ->from($iName, array('category_name'))
      ->joinLeft($uName, "$uName.category_id = $iName.category_id")
      ->group("$iName.category_id")
      ->where($uName.'.owner_id = ?', $user_id);

    return $table->fetchAll($select);
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
        'parent_id' => $params['classified_id'],
        'parent_type' => 'classified',
      );

      $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
      $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
      $photoFile->bridge($thumbFile, 'thumb.normal');

      $params['file_id'] = $photoFile->file_id; // This might be wrong
      $params['photo_id'] = $photoFile->file_id;

      // Remove temp files
      @unlink($mainName);
      @unlink($thumbName);
      
      /*
      $param['owner_type'] = $params['parent_type'];
      $param['owner_id'] = $params['parent_id'];
      unset($params['parent_type']);
      unset($params['parent_id']);
      */
    }

    $row = Engine_Api::_()->getDbtable('photos', 'classified')->createRow();
    $row->setFromArray($params);
    $row->save();
    return $row;
  }
}