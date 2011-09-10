<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Links.php 8226 2011-01-15 04:00:40Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Api_Links extends Core_Api_Abstract
{
  public function createLink(Core_Model_Item_Abstract $owner, $data)
  {
    $table = Engine_Api::_()->getDbtable('links', 'core');

    if( empty($data['parent_type']) || empty($data['parent_id']) )
    {
      $data['parent_type'] = $owner->getType();
      $data['parent_id'] = $owner->getIdentity();
    }

    $link = $table->createRow();
    $link->setFromArray($data);
    $link->owner_type = $owner->getType();
    $link->owner_id = $owner->getIdentity();
    $link->save();

    // Now try to create thumbnail
    $thumbnail = (string) @$data['thumb'];
    $thumbnail_parsed = @parse_url($thumbnail);
    //$ext = @ltrim(strrchr($thumbnail_parsed['path'], '.'), '.');
    //$link_parsed = @parse_url($link->uri);

    // Make sure to not allow thumbnails from domains other than the link (problems with subdomains, disabled for now)
    //if( $thumbnail && $thumbnail_parsed && $thumbnail_parsed['host'] === $link_parsed['host'] )
    //if( $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) )
    if( $thumbnail && $thumbnail_parsed )
    {
      $tmp_path = APPLICATION_PATH . '/temporary/link';
      $tmp_file = $tmp_path . '/' . md5($thumbnail);

      if( !is_dir($tmp_path) && !mkdir($tmp_path, 0777, true) ) {
        throw new Core_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
      }

      $src_fh = fopen($thumbnail, 'r');
      $tmp_fh = fopen($tmp_file, 'w');
      stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
      fclose($src_fh);
      fclose($tmp_fh);

      if( ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
        $ext = Engine_Image::image_type_to_extension($info[2]);
        $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;

        $image = Engine_Image::factory();
        $image->open($tmp_file)
          ->resize(120, 240)
          ->write($thumb_file)
          ->destroy();

        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
          'parent_type' => $link->getType(),
          'parent_id' => $link->getIdentity()
        ));

        $link->photo_id = $thumbFileRow->file_id;
        $link->save();

        @unlink($thumb_file);
      }

      @unlink($tmp_file);
    }

    return $link;
  }
}