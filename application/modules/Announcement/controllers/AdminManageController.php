<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminManageController.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Announcement_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->formFilter = $formFilter = new Announcement_Form_Admin_Filter();
    $page = $this->_getParam('page',1);

    if( /*$this->getRequest()->isPost() && */ $formFilter->isValid($this->_getAllParams()) )
    {
      $values = $formFilter->getValues();
      $paginator = $this->_helper->api()->announcement()->getPaginator($values);
      if ($values['orderby'] && $values['orderby_direction'] != 'DESC') {
        $this->view->orderby = $values['orderby'];
      }
    }
    else $paginator = $this->_helper->api()->announcement()->getPaginator();


    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    //$this->view->formDelete = new User_Form_Admin_Manage_Delete();
  }
  
  
  public function createAction()
  {
    $this->view->form = $form = new Announcement_Form_Admin_Create();

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      $params['user_id'] = $this->_helper->api()->user()->getViewer()->getIdentity();
      $announcement = $this->_helper->api()->getDbtable('announcements', 'announcement')->createRow();
      $announcement->setFromArray($params);
      $announcement->save();

      //increment statistics
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('announcement.creations');

      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }

  public function editAction()
  {
    $this->view->form = $form = new Announcement_Form_Admin_Edit();
    $id = $this->_getParam('id', null);
    $announcement = Engine_Api::_()->getItem('announcement', $id);

    // Save values
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      //$params['user_id'] = $this->_helper->api()->user()->getViewer()->getIdentity();
      //$announcement = $this->_helper->api()->getDbtable('announcements', 'announcement')->createRow();
      $announcement->setFromArray($params);
      $announcement->save();
      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
      //$form->addMessage('Changes Saved!');
    }
    
    // Initialize values
    else
    {
      $form->populate($announcement->toArray());
    }
  }

  public function deleteAction()
  {
    //$this->view->form = $form = new Announcement_Form_Admin_Edit();
    $this->view->id = $id = $this->_getParam('id', null);
    $announcement = Engine_Api::_()->getItem('announcement', $id);

    // Save values
    if( $this->getRequest()->isPost() )
    {
      $announcement->delete();
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }

  public function deleteselectedAction()
  {
    //$this->view->form = $form = new Announcement_Form_Admin_Edit();
    $this->view->ids = $ids = $this->_getParam('ids', null);
    $confirm = $this->_getParam('confirm', false);
    $this->view->count = count(explode(",", $ids));

    //$announcement = Engine_Api::_()->getItem('announcement', $id);

    // Save values
    if( $this->getRequest()->isPost() && $confirm == true )
    {
      $ids_array = explode(",", $ids);
      foreach( $ids_array as $id ){
        $announcement = Engine_Api::_()->getItem('announcement', $id);
        if( $announcement ) $announcement->delete();
      }
      
      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }
}