<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_IndexController extends Core_Controller_Action_User
{
  public function indexAction()
  {
    // Get rooms
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $select = $roomTable->select()
      ->where('public = ?', 1);

    $rooms = array();
    foreach( $roomTable->fetchAll($select) as $room ) {
      $rooms[$room->room_id] = $room->toRemoteArray();
    }
    $this->view->rooms = $rooms;
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->isOperator = $viewer->authorization()->isAllowed('admin');

    $this->view->canChat = $canChat = Engine_Api::_()->authorization()->isAllowed('chat', $viewer, 'chat');
    $this->view->canIM = $canIM = Engine_Api::_()->authorization()->isAllowed('chat', $viewer, 'im');

    $this->view->chatContainer = preg_replace('/[^a-z0-9]+/', '', $this->_getParam('tmpId'));
  }

  public function banAction()
  {
    
  }
}