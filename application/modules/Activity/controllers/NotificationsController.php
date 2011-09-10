<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: NotificationsController.php 7344 2010-09-10 21:01:44Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_NotificationsController extends Core_Controller_Action_Standard
{

  public function init()
  {
    $this->_helper->requireUser();
  }

  public function indexAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $this->view->requests = Engine_Api::_()->getDbtable('notifications', 'activity')->getRequestsPaginator($viewer);
    $notifications->setCurrentPageNumber($this->_getParam('page'));

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);

    $this->view->hasunread = false;

    // Now mark them all as read
    $ids = array();
    foreach( $notifications as $notification ) {
      $ids[] = $notification->notification_id;
    }
    //Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer, $ids);
  }

  public function hideAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer);
  }

  public function markreadAction()
  {
    $request = Zend_Controller_Front::getInstance()->getRequest();

    $action_id = $request->getParam('actionid', 0);

    $viewer = Engine_Api::_()->user()->getViewer();
    $notificationsTable = Engine_Api::_()->getDbtable('notifications', 'activity');
    $db = $notificationsTable->getAdapter();
    $db->beginTransaction();

    try {
      $notification = Engine_Api::_()->getItem('activity_notification', $action_id);
      $notification->read = 1;
      $notification->save();
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }

  public function updateAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $this->view->notificationCount = $notificationCount = (int) Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->notificationOnly = $request->getParam('notificationOnly', false);

    // @todo locale()->tonumber
    // array('%s update', '%s updates', $this->notificationCount), $this->locale()->toNumber($this->notificationCount));
    $this->view->text = $this->view->translate(array('%s Update', '%s Updates', $notificationCount), $notificationCount);
  }

  public function pulldownAction()
  {
    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $notifications->setCurrentPageNumber($page);

    if( $notifications->getCurrentItemCount() <= 0 || $page > $notifications->getCurrentPageNumber() ) {
      $this->_helper->viewRenderer->setNoRender(true);
      return;
    }

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);
  }

}