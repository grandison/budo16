<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Composer.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Plugin_Composer extends Core_Plugin_Abstract
{
  public function onAttachVideo($data)
  {
    if( !is_array($data) || empty($data['video_id']) ) {
      return;
    }

    $video = Engine_Api::_()->getItem('video', $data['video_id']);
    // update $video with new title and description
    $video->title = $data['title'];
    $video->description = $data['description'];
    $video->save();
    
    if( !($video instanceof Core_Model_Item_Abstract) || !$video->getIdentity() )
    {
      return;
    }

    return $video;
  }
}