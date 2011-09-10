<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Plugin_Menus
{
  public function canCreatePlaylists()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create playlists
    if( !Engine_Api::_()->authorization()->isAllowed('music_playlist', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewPlaylists()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Must be able to view playlists
    if( !Engine_Api::_()->authorization()->isAllowed('music_playlist', $viewer, 'view') ) {
      return false;
    }

    return true;
  }
}