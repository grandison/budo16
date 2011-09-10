<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 7994 2010-12-08 21:14:21Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Plugin_Menus
{
  public function canCreateEvents()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create events
    if( !Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewEvents()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    // Must be able to view events
    if( !Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_EventMainManage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$viewer->getIdentity() )
    {
      return false;
    }

    return true;
    /*
    return array(
      'label' => 'My Events',
      'route' => 'event_general',
      'params' => array(
        'action' => 'manage',
      )
    );
     *
     */
  }

  public function onMenuInitialize_EventMainCreate()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if( !$viewer->getIdentity() )
    {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('event', null, 'create') )
    {
      return false;
    }

    return true;
    /*
    return array(
      'label' => 'Create New Event',
      'route' => 'event_general',
      'params' => array(
        'action' => 'create',
      )
    );
     * 
     */
  }
  
  public function onMenuInitialize_EventProfileEdit()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }
    
    return array(
      'label' => 'Edit Event Details',
      'icon' => 'application/modules/Event/externals/images/edit.png',
      'route' => 'event_specific',
      'params' => array(
        'controller' => 'event',
        'action' => 'edit',
        'event_id' => $subject->getIdentity(),
        'ref' => 'profile'
      )
    );
  }

  public function onMenuInitialize_EventProfileStyle()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'style') )
    {
      return false;
    }

    return array(
      'label' => 'Edit Event Style',
      'icon' => 'application/modules/Event/externals/images/style.png',
      'class' => 'smoothbox',
      'route' => 'event_specific',
      'params' => array(
        'action' => 'style',
        'event_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      )
    );
  }

  public function onMenuInitialize_EventProfileMember()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('Whoops, not a event!');
    }

    if( !$viewer->getIdentity() )
    {
      return false;
    }

    $row = $subject->membership()->getRow($viewer);

    // Not yet associated at all
    if( null === $row )
    {
      if( $subject->membership()->isResourceApprovalRequired() ) {
        return array(
          'label' => 'Request Invite',
          'icon' => 'application/modules/Event/externals/images/member/join.png',
          'class' => 'smoothbox',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'event_id' => $subject->getIdentity(),
          ),
        );
      } else {
        return array(
          'label' => 'Join Event',
          'icon' => 'application/modules/Event/externals/images/member/join.png',
          'class' => 'smoothbox',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'event_id' => $subject->getIdentity()
          ),
        );
      }
    }

    // Full member
    // @todo consider owner
    else if( $row->active )
    {
      if( !$subject->isOwner($viewer) ) {
        return array(
          'label' => 'Leave Event',
          'icon' => 'application/modules/Event/externals/images/member/leave.png',
          'class' => 'smoothbox',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'event_id' => $subject->getIdentity()
          ),
        );
      } else {
        return array(
          'label' => 'Delete Event',
          'icon' => 'application/modules/Event/externals/images/delete.png',
          'class' => 'smoothbox',
          'route' => 'event_specific',
          'params' => array(
            'action' => 'delete',
            'event_id' => $subject->getIdentity()
          ),
        );
      }
    }

    else if( !$row->resource_approved && $row->user_approved )
    {
      return array(
        'label' => 'Cancel Invite Request',
        'icon' => 'application/modules/Event/externals/images/member/cancel.png',
        'class' => 'smoothbox',
        'route' => 'event_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'event_id' => $subject->getIdentity()
        ),
      );
    }

    else if( !$row->user_approved && $row->resource_approved )
    {
      return array(
        array(
          'label' => 'Accept Event Invite',
          'icon' => 'application/modules/Event/externals/images/member/accept.png',
          'class' => 'smoothbox',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'event_id' => $subject->getIdentity()
          ),
        ), array(
          'label' => 'Ignore Event Invite',
          'icon' => 'application/modules/Event/externals/images/member/reject.png',
          'class' => 'smoothbox',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'event_id' => $subject->getIdentity()
          ),
        )
      );
    }

    else
    {
      throw new Event_Model_Exception('An error has occurred.');
    }


    return false;
  }

  public function onMenuInitialize_EventProfileReport()
  {
    return false;
  }

  public function onMenuInitialize_EventProfileInvite()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('This event does not exist.');
    }
    if( !$subject->authorization()->isAllowed($viewer, 'invite') )
    {
      return false;
    }

    return array(
      'label' => 'Invite Guests',
      'icon' => 'application/modules/Event/externals/images/member/invite.png',
      'class' => 'smoothbox',
      'route' => 'event_extended',
      'params' => array(
        //'module' => 'event',
        'controller' => 'member',
        'action' => 'invite',
        'event_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_EventProfileShare()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('This event does not exist.');
    }

    if( !$viewer->getIdentity() )
    {
      return false;
    }
    
    return array(
      'label' => 'Share This Event',
      'icon' => 'application/modules/Event/externals/images/share.png',
      'class' => 'smoothbox',
      'route' => 'default',
      'params' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'share',
        'type' => $subject->getType(),
        'id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_EventProfileMessage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'event' )
    {
      throw new Event_Model_Exception('This event does not exist.');
    }

    if( !$viewer->getIdentity() || !$subject->isOwner($viewer))
    {
      return false;
    }

    return array(
      'label' => 'Message Members',
      'icon' => 'application/modules/Messages/externals/images/send.png',
      'route' => 'messages_general',
      'params' => array(
        'action' => 'compose',
        'to' => $subject->getIdentity(),
        'multi' => 'event'
      )
    );
  }
}