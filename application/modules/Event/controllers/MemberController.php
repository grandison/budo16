<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: MemberController.php 8015 2010-12-09 21:42:51Z jung $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Sami
 */
class Event_MemberController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( 0 !== ($event_id = (int) $this->_getParam('event_id')) &&
        null !== ($event = Engine_Api::_()->getItem('event', $event_id)) )
    {
      Engine_Api::_()->core()->setSubject($event);
    }

    $this->_helper->requireUser();
    $this->_helper->requireSubject('event');
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

    // Check resource approval
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();
    if( $subject->membership()->isResourceApprovalRequired() ) {
      $row = $subject->membership()->getReceiver()
        ->select()
        ->where('resource_id = ?', $subject->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->query()
        ->fetch(Zend_Db::FETCH_ASSOC, 0);
        ;
      if (empty($row)) {
        // has not yet requested an invite
        return $this->_helper->redirector->gotoRoute(array('action' => 'request', 'format' => 'smoothbox'));
      } elseif ($row['user_approved'] && !$row['resource_approved']) {
        // has requested an invite; show cancel invite page
        return $this->_helper->redirector->gotoRoute(array('action' => 'cancel', 'format' => 'smoothbox'));
      }
    }

    // Make form
    $this->view->form = $form = new Event_Form_Member_Join();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $membership_status = $subject->membership()->getRow($viewer)->active;
        
        $subject->membership()
          ->addMember($viewer)
          ->setUserApproved($viewer)
          ;

        $row = $subject->membership()
          ->getRow($viewer);

        $row->rsvp = $form->getValue('rsvp');
        $row->save();

        // Add activity if membership status was not valid from before
        if (!$membership_status){
          $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $activityApi->addActivity($viewer, $subject, 'event_join');
        }
        
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event joined')),
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
    $this->view->form = $form = new Event_Form_Member_Request();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->addMember($viewer)->setUserApproved($viewer);

        // Add notification
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'event_approve');

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your invite request has been sent.')),
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
    $this->view->form = $form = new Event_Form_Member_Cancel();
    
    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $user_id = $this->_getParam('user_id');
      $viewer = $this->_helper->api()->user()->getViewer();
      $subject = $this->_helper->api()->core()->getSubject();
      if( !$subject->authorization()->isAllowed($viewer, 'invite') &&
          $user_id != $viewer->getIdentity() &&
          $user_id ) {
        return;
      }

      if( $user_id ) {
        $user = Engine_Api::_()->getItem('user', $user_id);
        if( !$user ) {
          return;
        }
      } else {
        $user = $viewer;
      }
      
      $subject = Engine_Api::_()->core()->getSubject('event');
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();
      try
      {
        $subject->membership()->removeMember($user);
        
        // Remove the notification?
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
          $subject->getOwner(), $subject, 'event_approve');
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
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your invite request has been cancelled.')),
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
    $this->view->form = $form = new Event_Form_Member_Leave();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->removeMember($viewer);
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event left')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }
  
  public function acceptAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('event')->isValid() ) return;

    // Make form
    $this->view->form = $form = new Event_Form_Member_Join();

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

    // Process form
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $membership_status = $subject->membership()->getRow($viewer)->active;

      $subject->membership()->setUserApproved($viewer);

      $row = $subject->membership()
        ->getRow($viewer);

      $row->rsvp = $form->getValue('rsvp');
      $row->save();

      // Set the request as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'event_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }

      // Add activity
      if (!$membership_status){
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($viewer, $subject, 'event_join');
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

    $message = Zend_Registry::get('Zend_Translate')->_('You have accepted the invite to the event %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;

    if( $this->_helper->contextSwitch->getCurrentContext() == "smoothbox" ) {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event invite accepted')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function rejectAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('event')->isValid() ) return;

    // Make form
    $this->view->form = $form = new Event_Form_Member_Reject();

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

    // Process form
    $viewer = $this->_helper->api()->user()->getViewer();
    $subject = $this->_helper->api()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->removeMember($viewer);

      // Set the request as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'event_invite');
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
    $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the event %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;

    if( $this->_helper->contextSwitch->getCurrentContext() == "smoothbox" ) {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event invite rejected')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
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

    $event = Engine_Api::_()->core()->getSubject();

    if( !$event->membership()->isMember($user) ) {
      throw new Event_Model_Exception('Cannot remove a non-member');
    }

    // Make form
    $this->view->form = $form = new Event_Form_Member_Remove();

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $db = $event->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        // Remove membership
        $event->membership()->removeMember($user);

        // Remove the notification?
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
          $event->getOwner(), $event, 'event_approve');
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
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event member removed.')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }

  public function inviteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('event')->isValid() ) return;
    // @todo auth

    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->view->friends = $friends = $viewer->membership()->getMembers();

    // Prepare form
    $this->view->form = $form = new Event_Form_Invite();

    $count = 0;
    foreach( $friends as $friend )
    {

      if( $event->membership()->isMember($friend, null) ) 
	{
          continue;
	}
      $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
      $count++;
    }
    $this->view->count = $count;

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
    $table = $event->getTable();
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

        $event->membership()->addMember($friend)
          ->setResourceApproved($friend);

        $notifyApi->addNotification($friend, $viewer, $event, 'event_invite');
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
    if( !$this->_helper->requireSubject('event')->isValid() ) return;

    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      return $this->_helper->requireSubject->forward();
    }
    
    // Make form
    $this->view->form = $form = new Event_Form_Member_Approve();

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

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'event_accepted');

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event request approved')),
        'layout' => 'default-simple',
        'parentRefresh' => true,
      ));
    }
  }
}