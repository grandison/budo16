<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8459 2011-02-15 00:25:33Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_IndexController extends Core_Controller_Action_Standard
{

  public function init()
  {

    if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'view')->isValid() )
        return;

    $id = $this->_getParam('group_id', $this->_getParam('id', null));
    if( $id ) {
      $group = Engine_Api::_()->getItem('group', $id);
      if( $group ) {
        Engine_Api::_()->core()->setSubject($group);
      }
    }
  }

  public function browseAction()
  {
    // Navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('group_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('group_quick');

    // Form
    $this->view->formFilter = $formFilter = new Group_Form_Filter_Browse();
    $defaultValues = $formFilter->getValues();

    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      $formFilter->removeElement('view');
    }

    // Populate options
    foreach( $this->_helper->api()->getApi('core', 'group')->getCategories() as $row ) {
      $formFilter->category_id->addMultiOption($row->category_id, $row->title);
    }

    // Populate form data
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $this->view->formValues = $values = $formFilter->getValues();
    } else {
      $formFilter->populate($defaultValues);
      $this->view->formValues = $values = array();
    }

    // Prepare data
    $viewer = $this->_helper->api()->user()->getViewer();
    $this->view->formValues = $values = $formFilter->getValues();

    if( $viewer->getIdentity() && @$values['view'] == 1 ) {
      $values['users'] = array();
      foreach( $viewer->membership()->getMembersInfo(true) as $memberinfo ) {
        $values['users'][] = $memberinfo->user_id;
      }
    }

    $values['search'] = 1;

    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user');
    if( $user_id ) $values['user_id'] = $user_id;

    $this->view->paginator = $paginator = $this->_helper->api()->getApi('core', 'group')
            ->getGroupPaginator($values);

    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Check create
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('group', null, 'create');
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser->isValid() )
        return;
    if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'create')->isValid() )
        return;

    // Navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
          ->getNavigation('group_main');

    // Create form
    $this->view->form = $form = new Group_Form_Create();

    // Populate with categories
    foreach( Engine_Api::_()->getDbtable('categories', 'group')->fetchAll() as $row ) {
      $form->category_id->addMultiOption($row->category_id, $row->title);
    }

    if( count($form->category_id->getMultiOptions()) <= 1 ) {
      $form->removeElement('category_id');
    }

    // Check method/data validitiy
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();
    $viewer = $this->_helper->api()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();

    $db = Engine_Api::_()->getDbtable('groups', 'group')->getAdapter();
    $db->beginTransaction();

    try {
      // Create group
      $table = $this->_helper->api()->getDbtable('groups', 'group');
      $group = $table->createRow();
      $group->setFromArray($values);
      $group->save();

      // Add owner as member
      $group->membership()->addMember($viewer)
          ->setUserApproved($viewer)
          ->setResourceApproved($viewer);

      // Set photo
      if( !empty($values['photo']) ) {
        $group->setPhoto($form->photo);
      }

      // Process privacy
      $auth = Engine_Api::_()->authorization()->context;
      
      $roles = array('officer', 'member', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);
      $eventMax = array_search($values['auth_event'], $roles);
      $inviteMax = array_search($values['auth_invite'], $roles);

      $officerList = $group->getOfficerList();

      foreach( $roles as $i => $role ) {
        if( $role === 'officer' ) {
          $role = $officerList;
        }
        $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'photo', ($i <= $photoMax));
        $auth->setAllowed($group, $role, 'event', ($i <= $eventMax));
        $auth->setAllowed($group, $role, 'invite', ($i <= $inviteMax));
      }
      
      // Create some auth stuff for all officers
      $auth->setAllowed($group, $officerList, 'photo.edit', 1);
      $auth->setAllowed($group, $officerList, 'topic.edit', 1);

      // Add auth for invited users
      $auth->setAllowed($group, 'member_requested', 'view', 1);

      // Add action
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $group, 'group_create');
      if( $action ) {
        $activityApi->attachActivity($action, $group);
      }

      // Commit
      $db->commit();

      // Redirect
      return $this->_helper->redirector->gotoRoute(array('id' => $group->getIdentity()), 'group_profile', true);
    } catch( Engine_Image_Exception $e ) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }

  public function listAction()
  {
    
  }

  public function manageAction()
  {
    // Navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
          ->getNavigation('group_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('group_quick');
    
    // Form
    $this->view->formFilter = $formFilter = new Group_Form_Filter_Manage();
    $this->view->formValues = $values = $formFilter->getValues();

    // Populate form data
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $this->view->formValues = $values = $formFilter->getValues();
    } else {
      $formFilter->populate($defaultValues);
      $this->view->formValues = $values = array();
    }

    $viewer = $this->_helper->api()->user()->getViewer();
    $membership = Engine_Api::_()->getDbtable('membership', 'group');
    $select = $membership->getMembershipsOfSelect($viewer);
    $select->where('group_id IS NOT NULL');

    $table = Engine_Api::_()->getItemTable('group');
    $tName = $table->info('name');
    if( $values['view'] == 2 ) {
      $select->where("`{$tName}`.`user_id` = ?", $viewer->getIdentity());
    }
    if( !empty($values['text']) ) {
      $select->where(
          $table->getAdapter()->quoteInto("`{$tName}`.`title` LIKE ?", '%' . $values['text'] . '%') . ' OR ' .
          $table->getAdapter()->quoteInto("`{$tName}`.`description` LIKE ?", '%' . $values['text'] . '%')
      );
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->text = $values['text'];
    $this->view->view = $values['view'];
    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Check create
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('group', null, 'create');
  }
}