<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Api_Ad extends Core_Api_Abstract
{
  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;

  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;

  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getSelect($params));
  }

  public function getSelect($params = array())
  {
    $table = $this->api()->getDbtable('ads', 'core');
    
    $select = $table->select()
      ->order( 'ad_id DESC' );

    $select->limit(10);

    return $select;
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
      $photo_params = Array('parent_id'=>$params['owner_id'], 'parent_type'=>$params['owner_type']);

      try {
        $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
        $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
      }
      catch (Exception $e)
      {
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE)
	{
	  echo $e->getMessage();
          exit();
        }
      }

      $photoFile->bridge($thumbFile, 'thumb.normal');

      $params['file_id'] = $photoFile->file_id; // This might be wrong
      $params['photo_id'] = $photoFile->file_id;
    }

    // Remove temp files
    @unlink($mainName);
    @unlink($thumbName);

    
    $row = Engine_Api::_()->getDbtable('Adphotos', 'core')->createRow();
    $row->setFromArray($params);
    $row->save();
    return $row;

  }


  public function deleteAd($ad){
    // check to make sure the video did not fail, if it did we wont have files to remove
    if ($ad->media_type == 0){
      // get photo row
      $ad_photo = Engine_Api::_()->getItem('core_adphoto', $ad->photo_id);
      if( $ad_photo ){
        //delete storage item
        Engine_Api::_()->getItem('storage_file', $ad_photo->file_id)->remove();
        // delete photo row
        $ad_photo->delete();
      }
    }
    
    $ad->delete();
  }
}