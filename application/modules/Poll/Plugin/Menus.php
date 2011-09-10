<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Plugin_Menus
{
  public function canCreatePolls()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create polls
    if( !Engine_Api::_()->authorization()->isAllowed('poll', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewPolls()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Must be able to view polls
    if( !Engine_Api::_()->authorization()->isAllowed('poll', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_PollGutterList($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject instanceof User_Model_User ) {
      $user_id = $subject->getIdentity();
    } else if( $subject instanceof Poll_Model_Poll ) {
      $user_id = $subject->owner_id;
    } else {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['user_id'] = $user_id;
    return $params;
  }

  public function onMenuInitialize_PollGutterCreate($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->core()->getSubject('poll');

    if( !$poll->isOwner($viewer) ) {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('poll', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_PollGutterEdit($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->core()->getSubject('poll');

    if( !$poll->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['poll_id'] = $poll->getIdentity();
    return $params;
  }

  public function onMenuInitialize_PollGutterDelete($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->core()->getSubject('poll');

    if( !$poll->authorization()->isAllowed($viewer, 'delete') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['poll_id'] = $poll->getIdentity();
    return $params;
  }
}