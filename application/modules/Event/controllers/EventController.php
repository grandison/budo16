<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: EventController.php 8015 2010-12-09 21:42:51Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_EventController extends Core_Controller_Action_Standard
{
  public function init()
  {
    $id = $this->_getParam('event_id', $this->_getParam('id', null));
    if( $id )
    {
      $event = Engine_Api::_()->getItem('event', $id);
      if( $event )
      {
        Engine_Api::_()->core()->setSubject($event);
      }
    }
  }
  
  public function editAction()
  {
    $event_id = $this->getRequest()->getParam('event_id');
    $event = Engine_Api::_()->getItem('event', $event_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)) ) {
      return;
    }
    
    // Create form
    $event = Engine_Api::_()->core()->getSubject();
    $this->view->form = $form = new Event_Form_Edit(array('parent_type'=>$event->parent_type, 'parent_id'=>$event->parent_id));

    // Populate form options
    $categoryTable = Engine_Api::_()->getDbtable('categories', 'event');
    foreach( $categoryTable->fetchAll($categoryTable->select()->order('title ASC')) as $category ) {
      $form->category_id->addMultiOption($category->category_id, $category->title);
    }

    if( !$this->getRequest()->isPost() )
    {
      // Populate auth
      $auth = Engine_Api::_()->authorization()->context;

      if( $event->parent_type == 'group' ) {
        $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      foreach( $roles as $role ) {
	if( isset($form->auth_view->options[$role]) && $auth->isAllowed($event, $role, 'view') ) {
          $form->auth_view->setValue($role);
	}
	if( isset($form->auth_comment->options[$role]) && $auth->isAllowed($event, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
	}
	if( isset($form->auth_photo->options[$role]) && $auth->isAllowed($event, $role, 'photo') ) {
          $form->auth_photo->setValue($role);
	}
      }
      $form->auth_invite->setValue($auth->isAllowed($event, 'member', 'invite'));
      $form->populate($event->toArray());

      // Convert and re-populate times
      $start = strtotime($event->starttime);
      $end = strtotime($event->endtime);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($viewer->timezone);
      $start = date('Y-m-d H:i:s', $start);
      $end = date('Y-m-d H:i:s', $end);
      date_default_timezone_set($oldTz);

      $form->populate(array(
        'starttime' => $start,
        'endtime' => $end,
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }


    // Process
    $values = $form->getValues();

    // Convert times
    $oldTz = date_default_timezone_get();
    date_default_timezone_set($viewer->timezone);
    $start = strtotime($values['starttime']);
    $end = strtotime($values['endtime']);
    date_default_timezone_set($oldTz);
    $values['starttime'] = date('Y-m-d H:i:s', $start);
    $values['endtime'] = date('Y-m-d H:i:s', $end);
    
    // Check parent
    if( !isset($values['host']) && $event->parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
     $group = Engine_Api::_()->getItem('group', $event->parent_id);
     $values['host']  = $group->getTitle();
    }
    
    // Process
    $db = Engine_Api::_()->getItemTable('event')->getAdapter();
    $db->beginTransaction();

    try
    {
      // Set event info
      $event->setFromArray($values);
      $event->save();

      if( !empty($values['photo']) ) {
        $event->setPhoto($form->photo);
      }


      // Process privacy
      $auth = Engine_Api::_()->authorization()->context;

      if( $event->parent_type == 'group' ) {
        $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($event, $role, 'view',    ($i <= $viewMax));
        $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($event, $role, 'photo',   ($i <= $photoMax));
      }

      $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);
      
      // Commit
      $db->commit();
    }

    catch( Engine_Image_Exception $e )
    {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }


    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($event) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Redirect
    if( $this->_getParam('ref') === 'profile' ) {
      $this->_redirectCustom($event);
    } else {
      $this->_redirectCustom(array('route' => 'event_general', 'action' => 'manage'));
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
      if( $event->membership()->isMember($friend, null) ) continue;
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

 public function styleAction()
  {
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'style')->isValid() ) return;
    
    $user = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject('event');

    // Make form
    $this->view->form = $form = new Event_Form_Style();

    // Get current row
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
      ->where('type = ?', 'event')
      ->where('id = ?', $event->getIdentity())
      ->limit(1);

    $row = $table->fetchRow($select);

    // Check post
    if( !$this->getRequest()->isPost() )
    {
      $form->populate(array(
        'style' => ( null === $row ? '' : $row->style )
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Cool! Process
    $style = $form->getValue('style');

    // Save
    if( null == $row )
    {
      $row = $table->createRow();
      $row->type = 'event';
      $row->id = $event->getIdentity();
    }

    $row->style = $style;
    $row->save();

    $this->view->draft = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.');
    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => false,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'))
    ));
  }


  public function deleteAction()
  {

    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->getItem('event', $this->getRequest()->getParam('event_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    // Make form
    $this->view->form = $form = new Event_Form_Delete();

    if( !$event )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $event->delete();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected event has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'event_general', true),
      'messages' => Array($this->view->message)
    ));
  }








}