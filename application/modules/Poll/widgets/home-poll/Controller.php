<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8221 2011-01-15 00:24:02Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Widget_HomePollController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if( !$this->_getParam('poll_id') ) {
      return $this->setNoRender();
    }

    $poll = Engine_Api::_()->getItem('poll', $this->_getParam('poll_id'));
    if( !$poll ) {
      return $this->setNoRender();
    }

    $this->view->poll = $poll;
    $this->view->owner = $owner = $poll->getOwner();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->pollOptions = $poll->getOptions();
    $this->view->hasVoted = $poll->viewerVoted();
    $this->view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showPieChart', false);
    $this->view->canVote = $poll->authorization()->isAllowed(null, 'vote');
    $this->view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canChangeVote', false);
    $this->view->hideLinks = true;
  }

  public function adminAction()
  {
    // Check auth
    if( !Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view') ) {
      return $this->setNoRender();
    }
    
    $this->view->form = $form = new Poll_Form_Admin_Widget_HomePoll();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $this->view->values = $form->getValues();
  }
}