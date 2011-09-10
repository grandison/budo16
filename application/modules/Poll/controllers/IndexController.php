<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8368 2011-02-01 03:59:27Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_IndexController extends Core_Controller_Action_Standard
{
  public function init()
  {
    // Get subject
    $poll = null;
    if( null !== ($pollIdentity = $this->_getParam('poll_id')) ) {
      $poll = Engine_Api::_()->getItem('poll', $pollIdentity);
      if( null !== $poll ) {
        Engine_Api::_()->core()->setSubject($poll);
      }
    }

    // Get viewer
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // only show polls if authorized
    $resource = ( $poll ? $poll : 'poll' );
    $viewer = ( $viewer && $viewer->getIdentity() ? $viewer : null );
    if( !$this->_helper->requireAuth()->setAuthParams($resource, $viewer, 'view')->isValid() ) {
      return;
    }
  }

  public function browseAction()
  {
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_quick');

    // Get form
    $this->view->form = $form = new Poll_Form_Search();

    // Process form
    $values = array('browse' => 1);
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    }
    $this->view->formValues = array_filter($values);

    $viewer = Engine_Api::_()->user()->getViewer();
    if( @$values['show'] == 2 && $viewer->getIdentity() ) {
      // Get an array of friend ids
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    }
    unset($values['show']);

    // Make paginator
    $currentPageNumber = $this->_getParam('page', 1);
    $itemCountPerPage = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.perPage', 10);
    
    $this->view->paginator = $paginator = Engine_Api::_()->poll()->getPollsPaginator($values);
    $paginator
      ->setItemCountPerPage($itemCountPerPage)
      ->setCurrentPageNumber($currentPageNumber)
      ;

    // Check create
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('poll', null, 'create');
  }

  public function viewAction()
  {
    // Check auth
    if( !$this->_helper->requireSubject('poll')->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) {
      return;
    }

    $this->view->poll = $poll = Engine_Api::_()->core()->getSubject('poll');
    $this->view->owner = $owner = $poll->getOwner();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->pollOptions = $poll->getOptions();
    $this->view->hasVoted = $poll->viewerVoted();
    $this->view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showPieChart', false);
    $this->view->canVote = $poll->authorization()->isAllowed(null, 'vote');
    $this->view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canChangeVote', false);

    if( !$owner->isSelf($viewer) ) {
      $poll->view_count++;
      $poll->save();
    }
  }

  public function voteAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'vote')->isValid() ) {
      return;
    }

    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    
    $option_id = $this->_getParam('option_id');
    $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canChangeVote', false);

    $poll = Engine_Api::_()->core()->getSubject('poll');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if( !$poll ) {
      $this->view->success = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('This poll does not seem to exist anymore.');
      return;
    }

    if( $poll->hasVoted($viewer) && !$canChangeVote ) {
      $this->view->success = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('You have already voted on this poll, and are not permitted to change your vote.');
      return;
    }

    $db = Engine_Api::_()->getDbtable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      $poll->vote($viewer, $option_id);

      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      $this->view->success = false;
      throw $e;
    }
    
    $this->view->success = true;
    $pollOptions = array();
    foreach( $poll->getOptions()->toArray() as $option ) {
      $option['votesTranslated'] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
      $pollOptions[] = $option;
    }
    $this->view->pollOptions = $pollOptions;
    $this->view->votes_total = $poll->vote_count;
  }

  /* Owner */

  public function manageAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams('poll', null, 'create')->isValid() ) {
      return;
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_quick');

    // Get form
    $this->view->form = $form = new Poll_Form_Search();
    $form->removeElement('show');

    // Process form
    $this->view->owner = $owner = Engine_Api::_()->user()->getViewer();
    $this->view->user_id = $owner->getIdentity();
    $values = array();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    }
    $this->view->formValues = array_filter($values);
    $values['user_id'] = $owner->getIdentity();

    // Make paginator
    $currentPageNumber = $this->_getParam('page', 1);
    $itemCountPerPage = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.perPage', 10);

    $this->view->paginator = $paginator = Engine_Api::_()->poll()->getPollsPaginator($values);
    $paginator
      ->setItemCountPerPage($itemCountPerPage)
      ->setCurrentPageNumber($currentPageNumber)
      ;

    // Check create
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('poll', null, 'create');
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams('poll', null, 'create')->isValid() ) {
      return;
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_main');

    $this->view->options = array();
    $this->view->maxOptions = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.maxoptions', 15);
    $this->view->form = $form = new Poll_Form_Create();

    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Check options
    $options = (array) $this->_getParam('optionsArray');
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $this->view->options = $options;
    if( empty($options) || !is_array($options) || count($options) < 2 ) {
      return $form->addError('You must provide at least two possible answers.');
    }
    foreach( $options as $index => $option ) {
      if( strlen($option) > 80 ) {
        $options[$index] = Engine_String::substr($option, 0, 80);
      }
    }

    // Process
    $pollTable = Engine_Api::_()->getItemTable('poll');
    $pollOptionsTable = Engine_Api::_()->poll()->api()->getDbtable('options', 'poll');
    $db = $pollTable->getAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();
      $values['user_id'] = $viewer->getIdentity();
      
      // Create poll
      $poll = $pollTable->createRow();
      $poll->setFromArray($values);
      $poll->save();

      // Create options
      $censor = new Engine_Filter_Censor();
      foreach( $options as $option ) {
        $pollOptionsTable->insert(array(
          'poll_id' => $poll->getIdentity(),
          'poll_option' => $censor->filter($option),
        ));
      }

      // Privacy
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = array('everyone');
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = array('everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $auth->setAllowed($poll, 'registered', 'vote', true);

      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }

    // Process activity
    $db = Engine_Api::_()->getDbTable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $poll, 'poll_new');
      if( $action ) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $poll);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }

    // Redirect
    return $this->_helper->redirector->gotoUrl($poll->getHref(), array('prependBase' => false));
  }

  public function editAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) {
      return;
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('poll_main');

    // Setup
    $viewer = $this->_helper->api()->user()->getViewer();
    $poll = Engine_Api::_()->core()->getSubject('poll');
    
    // Get form
    $this->view->form = $form = new Poll_Form_Edit();
    $form->removeElement('title');
    $form->removeElement('description');
    $form->removeElement('options');

    // Prepare privacy
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    // Populate form with current settings
    $form->search->setValue($poll->search);
    foreach( $roles as $role ) {
      if( 1 === $auth->isAllowed($poll, $role, 'view') ) {
        $form->auth_view->setValue($role);
      }
      if( 1 === $auth->isAllowed($poll, $role, 'comment') ) {
        $form->auth_comment->setValue($role);
      }
    }

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();

      // CREATE AUTH STUFF HERE
      if( empty($values['auth_view']) ) {
        $values['auth_view'] = array('everyone');
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = array('everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $poll->search = (bool) $values['search'];
      $poll->save();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($poll) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'poll_general', true);
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($poll, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $this->view->form = $form = new Poll_Form_Delete();

    if( !$poll )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $poll->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $poll->delete();
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your poll has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'poll_general', true),
      'messages' => Array($this->view->message)
    ));

  }
}