<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Plugin_Menus
{
  public function canCreateAlbums()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create albums
    if( !Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewAlbums()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Must be able to view albums
    if( !Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'view') ) {
      return false;
    }

    return true;
  }
}