<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Plugin_Menus
{
  public function onMenuInitialize_VideoMainManage($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_VideoMainCreate($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'create') ) {
      return false;
    }

    return true;
  }
}