<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Adphoto.php 8215 2011-01-14 07:40:36Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_Adphoto extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getPhotoUrl($type = null)
  {
    if( empty($this->file_id) ) {
      return "no file id";
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
    if( !$file ) {
      return "no file";
    }

    return $file->map();
  }
}