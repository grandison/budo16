<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Plugin_Menus
{
  public function canCreateClassifieds()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create classifieds
    if( !Engine_Api::_()->authorization()->isAllowed('classified', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewClassifieds()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Must be able to view classifieds
    if( !Engine_Api::_()->authorization()->isAllowed('classified', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_ClassifiedGutterList($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject instanceof User_Model_User ) {
      $user_id = $subject->getIdentity();
    } else if( $subject instanceof Classified_Model_Classified ) {
      $user_id = $subject->owner_id;
    } else {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['user_id'] = $user_id;
    return $params;
  }

  public function onMenuInitialize_ClassifiedGutterCreate($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->core()->getSubject('classified');

    if( !$classified->isOwner($viewer) ) {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('classified', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_ClassifiedGutterEdit($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->core()->getSubject('classified');

    if( !$classified->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['classified_id'] = $classified->getIdentity();
    return $params;
  }

  public function onMenuInitialize_ClassifiedGutterDelete($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->core()->getSubject('classified');

    if( !$classified->authorization()->isAllowed($viewer, 'delete') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['classified_id'] = $classified->getIdentity();
    return $params;
  }
}