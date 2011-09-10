<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Menus.php 8412 2011-02-08 23:24:28Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Plugin_Menus
{
  public function canCreateGroups()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create events
    if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewGroups()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    // Must be able to view events
    if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_GroupMainManage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$viewer->getIdentity() )
    {
      return false;
    }
    return true;
  }

  public function onMenuInitialize_GroupMainCreate()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if( !$viewer->getIdentity() )
    {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('group', null, 'create') )
    {
      return false;
    }

    return true;
  }
  
  public function onMenuInitialize_GroupProfileEdit()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
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
      'label' => 'Edit Group Details',
      'icon' => 'application/modules/Group/externals/images/edit.png',
      'route' => 'group_specific',
      'params' => array(
        'controller' => 'group',
        'action' => 'edit',
        'group_id' => $subject->getIdentity(),
        'ref' => 'profile'
      )
    );
  }

  public function onMenuInitialize_GroupProfileStyle()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
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
      'label' => 'Edit Group Style',
      'icon' => 'application/modules/Group/externals/images/style.png',
      'class' => 'smoothbox',
      'route' => 'group_specific',
      'params' => array(
        'action' => 'style',
        'group_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      )
    );
  }

  public function onMenuInitialize_GroupProfileMember()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
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
          'label' => 'Request Membership',
          'icon' => 'application/modules/Group/externals/images/member/join.png',
          'class' => 'smoothbox',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'group_id' => $subject->getIdentity(),
          ),
        );
      } else {
        return array(
          'label' => 'Join Group',
          'icon' => 'application/modules/Group/externals/images/member/join.png',
          'class' => 'smoothbox',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'group_id' => $subject->getIdentity()
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
          'label' => 'Leave Group',
          'icon' => 'application/modules/Group/externals/images/member/leave.png',
          'class' => 'smoothbox',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'group_id' => $subject->getIdentity()
          ),
        );
      } else {
        return array(
          'label' => 'Delete Group',
          'icon' => 'application/modules/Group/externals/images/delete.png',
          'class' => 'smoothbox',
          'route' => 'group_specific',
          'params' => array(
            'action' => 'delete',
            'group_id' => $subject->getIdentity()
          ),
        );
      }
    }

    else if( !$row->resource_approved && $row->user_approved )
    {
      return array(
        'label' => 'Cancel Membership Request',
        'icon' => 'application/modules/Group/externals/images/member/cancel.png',
        'class' => 'smoothbox',
        'route' => 'group_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'group_id' => $subject->getIdentity()
        ),
      );
    }

    else if( !$row->user_approved && $row->resource_approved )
    {
      return array(
        array(
          'label' => 'Accept Membership Request',
          'icon' => 'application/modules/Group/externals/images/member/accept.png',
          'class' => 'smoothbox',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'group_id' => $subject->getIdentity()
          ),
        ), array(
          'label' => 'Ignore Membership Request',
          'icon' => 'application/modules/Group/externals/images/member/reject.png',
          'class' => 'smoothbox',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'group_id' => $subject->getIdentity()
          ),
        )
      );
    }

    else
    {
      throw new Group_Model_Exception('Wow, something really strange happened.');
    }


    return false;
  }

  public function onMenuInitialize_GroupProfileReport()
  {
    return false;
  }

  public function onMenuInitialize_GroupProfileInvite()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if( $subject->getType() !== 'group' ) {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$subject->authorization()->isAllowed($viewer, 'invite') ) {
      return false;
    }

    return array(
      'label' => 'Invite Members',
      'icon' => 'application/modules/Group/externals/images/member/invite.png',
      'class' => 'smoothbox',
      'route' => 'group_extended',
      'params' => array(
        //'module' => 'group',
        'controller' => 'member',
        'action' => 'invite',
        'group_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_GroupProfileShare()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() )
    {
      return false;
    }
    
    return array(
      'label' => 'Share Group',
      'icon' => 'application/modules/Group/externals/images/share.png',
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

  public function onMenuInitialize_GroupProfileMessage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' ) {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() || !$subject->isOwner($viewer) ) {
      return false;
    }

    return array(
      'label' => 'Message Members',
      'icon' => 'application/modules/Messages/externals/images/send.png',
      'route' => 'messages_general',
      'params' => array(
        'action' => 'compose',
        'to' => $subject->getIdentity(),
        'multi' => 'group'
      )
    );
  }
}