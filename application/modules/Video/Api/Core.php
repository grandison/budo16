<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8421 2011-02-09 04:40:41Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Api_Core extends Core_Api_Abstract
{
  public function getVideosPaginator($params = array())
  {
    $paginator = Zend_Paginator::factory($this->getVideosSelect($params));
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

  public function getVideosSelect($params = array())
  {
    $table = Engine_Api::_()->getDbtable('videos', 'video');
    $rName = $table->info('name');

    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');
    
    $select = $table->select()
      ->from($table->info('name'))
      ->order( !empty($params['orderby']) ? $params['orderby'].' DESC' : "$rName.creation_date DESC" );
    
    if( !empty($params['text']) ) {
      $searchTable = Engine_Api::_()->getDbtable('search', 'core');
      $db = $searchTable->getAdapter();
      $sName = $searchTable->info('name');
      $select
        ->joinRight($sName, $sName . '.id=' . $rName . '.video_id', null)
        ->where($sName . '.type = ?', 'video')
        ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['text'])))
        //->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['text'])))
        ;
    }
      
    if( !empty($params['status']) && is_numeric($params['status']) )
    {
      $select->where($rName.'.status = ?', $params['status']);
    }
    if( !empty($params['search']) && is_numeric($params['search']) )
    {
      $select->where($rName.'.search = ?', $params['search']);
    }
    if( !empty($params['user_id']) && is_numeric($params['user_id']) )
    {
      $select->where($rName.'.owner_id = ?', $params['user_id']);
    }

    if( !empty($params['user']) && $params['user'] instanceof User_Model_User )
    {
      $select->where($rName.'.owner_id = ?', $params['user_id']->getIdentity());
    }
    
    if( !empty($params['category']) )
    {
      $select->where($rName.'.category_id = ?', $params['category']);
    }

    if( !empty($params['tag']) )
    {
      $select
        // ->setIntegrityCheck(false)
        // ->from($rName)
        ->joinLeft($tmName, "$tmName.resource_id = $rName.video_id", NULL)
        ->where($tmName.'.resource_type = ?', 'video')
        ->where($tmName.'.tag_id = ?', $params['tag']);
    }

    return $select;
  }

  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'video');
    return $table->fetchAll($table->select()->order('category_name ASC'));
  }

  public function getCategory($category_id)
  {
    return Engine_Api::_()->getDbtable('categories', 'video')->find($category_id)->current();
  }

  public function getRating($video_id)
  {
    $table  = Engine_Api::_()->getDbTable('ratings', 'video');
    $rating_sum = $table->select()
      ->from($table->info('name'), new Zend_Db_Expr('SUM(rating)'))
      ->group('video_id')
      ->where('video_id = ?', $video_id)
      ->query()
      ->fetchColumn(0)
      ;

    $total = $this->ratingCount($video_id);
    if ($total) $rating = $rating_sum/$this->ratingCount($video_id);
    else $rating = 0;
    
    return $rating;
  }

  public function getRatings($video_id)
  {
    $table  = Engine_Api::_()->getDbTable('ratings', 'video');
    $rName = $table->info('name');
    $select = $table->select()
                    ->from($rName)
                    ->where($rName.'.video_id = ?', $video_id);
    $row = $table->fetchAll($select);
    return $row;
  }
  
  public function checkRated($video_id, $user_id)
  {
    $table  = Engine_Api::_()->getDbTable('ratings', 'video');

    $rName = $table->info('name');
    $select = $table->select()
                 ->setIntegrityCheck(false)
                    ->where('video_id = ?', $video_id)
                    ->where('user_id = ?', $user_id)
                    ->limit(1);
    $row = $table->fetchAll($select);
    
    if (count($row)>0) return true;
    return false;
  }

  public function setRating($video_id, $user_id, $rating){
    $table  = Engine_Api::_()->getDbTable('ratings', 'video');
    $rName = $table->info('name');
    $select = $table->select()
                    ->from($rName)
                    ->where($rName.'.video_id = ?', $video_id)
                    ->where($rName.'.user_id = ?', $user_id);
    $row = $table->fetchRow($select);
    if (empty($row)) {
      // create rating
      Engine_Api::_()->getDbTable('ratings', 'video')->insert(array(
        'video_id' => $video_id,
        'user_id' => $user_id,
        'rating' => $rating
      ));
    }
/*
    $select = $table->select()
      //->setIntegrityCheck(false)
      ->from($rName)
      ->where($rName.'.video_id = ?', $video_id);

    $row = $table->fetchAll($select);
    $total = count($row);
    foreach( $row as $item )
    {
      $rating += $item->rating;
    }
    $video = Engine_Api::_()->getItem('video', $video_id);
    $video->rating = $rating/$total;
    $video->save();*/
    
  }

  public function ratingCount($video_id){
    $table  = Engine_Api::_()->getDbTable('ratings', 'video');
    $rName = $table->info('name');
    $select = $table->select()
                    ->from($rName)
                    ->where($rName.'.video_id = ?', $video_id);
    $row = $table->fetchAll($select);
    $total = count($row);
    return $total;
  }

  // handle video upload
  public function createVideo($params, $file, $values)
  {
    if( $file instanceof Storage_Model_File ) {
      $params['file_id'] = $file->getIdentity();
    } else {
      // create video item
      $video = Engine_Api::_()->getDbtable('videos', 'video')->createRow();
      $file_ext = pathinfo($file['name']);
      $file_ext = $file_ext['extension'];
      $video->code = $file_ext;
      $video->save();

      // Store video in temporary storage object for ffmpeg to handle
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $storageObject = $storage->createFile($file, array(
        'parent_id' => $video->getIdentity(),
        'parent_type' => $video->getType(),
        'user_id' => $video->owner_id,
      ));
      // Remove temporary file
      @unlink($file['tmp_name']);

      $video->file_id = $storageObject->file_id;
      $video->save();

      // Store video in temp directory for ffmpeg to handle
//      $tmp_file = APPLICATION_PATH . '/temporary/video/'.$video->video_id.".".$file_ext;
//      $tmp_path = dirname($tmp_file);
//      if( !file_exists($tmp_path) ) {
//        mkdir($tmp_path, 0777, true);
//      }
//      $src_fh = fopen($file['tmp_name'], 'r');
//      $tmp_fh = fopen($tmp_file, 'w');
//      stream_copy_to_stream($src_fh, $tmp_fh);
      
      // Add to jobs
      Engine_Api::_()->getDbtable('jobs', 'core')->addJob('video_encode', array(
        'video_id' => $video->getIdentity(),
      ));
    }
    //$video->save();
    return $video;
  }

  public function deleteVideo($video)
  {

    // delete video ratings
    Engine_Api::_()->getDbtable('ratings', 'video')->delete(array(
      'video_id = ?' => $video->video_id,
    ));

    // check to make sure the video did not fail, if it did we wont have files to remove
    if ($video->status == 1){
      // delete storage files (video file and thumb)
      if ($video->type == 3) Engine_Api::_()->getItem('storage_file', $video->file_id)->remove();
      if ($video->photo_id) Engine_Api::_()->getItem('storage_file', $video->photo_id)->remove();
    }
    
    // delete activity feed and its comments/likes
    Engine_Api::_()->getItem('video', $video->video_id)->delete();


  }
}
