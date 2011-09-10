<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminManageController.php 7493 2010-09-29 04:08:05Z shaun $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('chat_admin_main', array(), 'chat_admin_main_manage');

    // Build paginator
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $select = $roomTable->select();    
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
  }

  public function createAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $this->view->form = $form = new Chat_Form_Admin_Room_Create();

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $room = $roomTable->createRow();
      $room->setFromArray($form->getValues());
      $room->save();

      $this->view->form = null;
    }

    $this->renderScript('admin-manage/form.tpl');
  }

  public function editAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $this->view->form = $form = new Chat_Form_Admin_Room_Edit();

    if( null === ($id = $this->_getParam('id')) ) {
      throw new Exception('no id');
    }

    $room = $roomTable->find($id)->current();
    if( !$room ) {
      throw new Exception('missing room');
    }

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $room->setFromArray($form->getValues());
      $room->save();

      $this->view->form = null;
    }

    $this->renderScript('admin-manage/form.tpl');
  }

  public function deleteAction()
  {
    $roomTable = Engine_Api::_()->getDbtable('rooms', 'chat');
    $this->view->form = $form = new Chat_Form_Admin_Room_Delete();

    if( null === ($id = $this->_getParam('id')) ) {
      throw new Exception('no id');
    }

    $room = $roomTable->find($id)->current();
    if( !$room ) {
      throw new Exception('missing room');
    }

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $room->delete();

      $this->view->form = null;
    }

    $this->renderScript('admin-manage/form.tpl');
  }
}