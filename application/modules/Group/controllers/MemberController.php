<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: MemberController.php 8398 2011-02-04 21:26:24Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_MemberController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( 0 !== ($group_id = (int) $this->_getParam('group_id')) &&
        null !== ($group = Engine_Api::_()->getItem('group', $group_id)) )
    {
      Engine_Api::_()->core()->setSubject($group);
    }

    $this->_helper->requireUser();
    $this->_helper->requireSubject('group');
    /*
    $this->_helper->requireAuth()->setAuthParams(
      null,
      null,
      null
      //'edit'
    );
     *
     */
  }

  public function joinAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();

    // Make form
    $this->view->form = $form = new Group_Form_Member_Join();

    // If member is already part of the group
    if( $subject->membership()->isMember($viewer) ) {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        // Set the request as handled
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
          $viewer, $subject, 'group_invite');
        if( $notification )
        {
          $notification->mitigated = true;
          $notification->save();
        }
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('You are already a member of this group.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->addMember($viewer)->setUserApproved($viewer);

        // Set the request as handled
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
          $viewer, $subject, 'group_invite');
        if( $notification )
        {
          $notification->mitigated = true;
          $notification->save();
        }

        // Add activity
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($viewer, $subject, 'group_join');

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('You are now a member of this group.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function requestAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;

    // Make form
    $this->view->form = $form = new Group_Form_Member_Request();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      $owner = $subject->getOwner();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->addMember($viewer)->setUserApproved($viewer);
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'group_approve');
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Group membership request sent')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function cancelAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;

    // Make form
    $this->view->form = $form = new Group_Form_Member_Cancel();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->removeMember($viewer);

        // Remove the notification?
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
          $subject->getOwner(), $subject, 'group_approve');
        if( $notification ) {
          $notification->delete();
        }

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Group membership request cancelled.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function leaveAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;
    
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();

    if( $subject->isOwner($viewer) ) return;

    // Make form
    $this->view->form = $form = new Group_Form_Member_Leave();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {

      $list = $subject->getOfficerList();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        // remove from officer list
        $list->remove($viewer);
        
        $subject->membership()->removeMember($viewer);
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully left this group.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }
  
  public function acceptAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('group')->isValid() ) return;

    // Make form
    $this->view->form = $form = new Group_Form_Member_Accept();

    // Process form
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Data');
      return;
    }

    // Process 
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->setUserApproved($viewer);

      // Set the request as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'group_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }

      // Add activity
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $subject, 'group_join');

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->error = false;
    
    $message = Zend_Registry::get('Zend_Translate')->_('You have accepted the invite to the group %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;
    
    if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array($message),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function rejectAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('group')->isValid() ) return;

    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      $user = $this->_helper->api()->user()->getViewer();
      //return $this->_helper->requireSubject->forward();
    }

    // Make form
    $this->view->form = $form = new Group_Form_Member_Reject();

    // Process form
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Data');
      return;
    }

    // Process
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->removeMember($user);

      // Set the request as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
        $user, $subject, 'group_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->error = false;
    $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the group %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;

    if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array($message),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }






  
  public function promoteAction()
  {
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }

    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$group->membership()->isMember($user) ) {
      throw new Group_Model_Exception('Cannot add a non-member as an officer');
    }

    $this->view->form = $form = new Group_Form_Member_Promote();

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    $table = $list->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $list->add($user);

      // Add notification
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      $notifyApi->addNotification($user, $viewer, $group, 'group_promote');

      // Add activity
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($user, $group, 'group_promote');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Member Promoted')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }

  public function demoteAction()
  {
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }

    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();

    if( !$group->membership()->isMember($user) ) {
      throw new Group_Model_Exception('Cannot remove a non-member as an officer');
    }

    $this->view->form = $form = new Group_Form_Member_Demote();

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    $table = $list->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $list->remove($user);

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Member Demoted')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }

  public function removeAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;
    
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }

    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();

    if( !$group->membership()->isMember($user) ) {
      throw new Group_Model_Exception('Cannot remove a non-member');
    }

    // Make form
    $this->view->form = $form = new Group_Form_Member_Remove();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $db = $group->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        // Remove as officer first (if necessary)
        $list->remove($user);

        // Remove membership
        $group->membership()->removeMember($user);

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Group member removed.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function inviteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('group')->isValid() ) return;
    // @todo auth

    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->group = $group = Engine_Api::_()->core()->getSubject();
    $this->view->friends = $friends = $viewer->membership()->getMembers();

    // Prepare form
    $this->view->form = $form = new Group_Form_Invite();

    $count = 0;
    foreach( $friends as $friend )
    {
      if( $group->membership()->isMember($friend, null) ) continue;
      $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
      $count++;
    }
    $this->view->count = $count;

    // throw notice if count = 0
    if( $count == 0 ) {
      return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have no friends you can invite.')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
      ));
    }

    // Not posting
    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }


    // Process
    $table = $group->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $usersIds = $form->getValue('users');
      
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      foreach( $friends as $friend )
      {
        if( !in_array($friend->getIdentity(), $usersIds) )
        {
          continue;
        }

        $group->membership()->addMember($friend)
          ->setResourceApproved($friend);

        $notifyApi->addNotification($friend, $viewer, $group, 'group_invite');
      }


      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Members invited')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }


  public function approveAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('group')->isValid() ) return;

    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }
    
    // Make form
    $this->view->form = $form = new Group_Form_Member_Approve();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->setResourceApproved($user);

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'group_accepted');

        // Add activity
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($user, $subject, 'group_join');

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Group request approved')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function editAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('group')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }

    $group = Engine_Api::_()->core()->getSubject('group');
    $memberInfo = $group->membership()->getMemberInfo($user);

    // Make form
    $this->view->form = $form = new Group_Form_Member_Edit();

    if( !$this->getRequest()->isPost() )
    {
      $form->populate(array(
        'title' => $memberInfo->title
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }
    
    $db = $group->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $memberInfo->setFromArray($form->getValues());
      $memberInfo->save();
      
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Member title changed')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }
}