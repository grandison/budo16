<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminManageController.php 8558 2011-03-04 01:12:51Z jung $
 * @author     Sami
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_AdminManageController extends Core_Controller_Action_Admin
{
  public function init()
  {
    $id = $this->_getParam('id', $this->_getParam('network_id', null));
    if( $id )
    {
      $network = Engine_Api::_()->getItem('network', $id);
      if( $network )
      {
        Engine_Api::_()->core()->setSubject($network);
      }
    }
  }
  
  public function indexAction()
  {
    $this->view->formFilter = $formFilter = new Network_Form_Admin_Filter();

    $page = $this->_getParam('page', 1);

    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) )
    {
      $values = $formFilter->getValues();
    }
    $this->view->formValues = $values;

    // Prepare query
    $networkTable = Engine_Api::_()->getDbtable('networks', 'network');
    $select = $networkTable->select();

    // Apply params
    if( !empty($values['order']) ) {
      $select->order($values['order'] . ' ' . ( !empty($values['direction']) ? $values['direction'] : 'ASC'));
    }
    
    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($page);

    // Fields
    $this->view->fields = Engine_Api::_()->fields()->getFieldsMeta("user");
  }

  public function createAction()
  {
    $this->view->form = $form = new Network_Form_Admin_Network();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $table = Engine_Api::_()->getDbtable('networks', 'network');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $network = $table->createRow();
      $network->setFromArray($values);
      $network->save();

      // Sort
      $network->recalculateAll();

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
  }

  public function editAction()
  {
    if( !$this->_helper->requireSubject('network') ) return;
    
    $network = Engine_Api::_()->core()->getSubject();
    
    $this->view->form = $form = new Network_Form_Admin_Network();

    if( !$this->getRequest()->isPost() ) {
      $form->populate($network->toFormArray());
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $table = $network->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $originalValues = $network->toFormArray();
      
      $network->setFromArray($values);
      $network->save();

      // Sort if field_id or assignment changed
      if( @$originalValues['field_id']   != @$values['field_id']   ||
          @$originalValues['pattern']    != @$values['pattern']    ||
          @$originalValues['assignment'] != @$values['assignment'] ) {

        $network->recalculateAll();
      }

      $db->commit();

      $form->addNotice('Changes saved!');
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireSubject('network') ) return;
    
    $this->view->form = $form = new Network_Form_Admin_Delete();
    $form->setAttrib('class', 'global_form_popup');

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $network = Engine_Api::_()->core()->getSubject();
      if( !$network instanceof Network_Model_Network )
      {
        throw new Exception('bleh');
      }

      $db = $network->getTable()->getAdapter(); //Engine_Api::_()->getDbtable('networks', 'network')->getAdapter();
      $db->beginTransaction();

      try
      {
        $network->membership()->removeAllMembers();
        $network->delete();
        Engine_Api::_()->core()->clearSubject();

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
        return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
      } else {
        $this->view->status = true;
      }
    }
  }

  public function deleteselectedAction()
  {
    $this->view->ids = $ids = $this->_getParam('actions', $this->_getParam('ids', array()));
    $confirm = $this->_getParam('confirm', false);

    if( is_string($ids) ) {
      $ids = explode(',', $ids);
    }
    $this->view->idsString = join(',', $ids);
    $this->view->count = count($ids);

    // Save values
    if( $this->getRequest()->isPost() && $confirm == true )
    {
      // delete network membership
      $table = Engine_Api::_()->getItemTable('network');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        foreach( $ids as $id ) {
          $network = Engine_Api::_()->getItem('network', $id);
          if( $network instanceof Network_Model_Network ) {
            $network->membership()->removeAllMembers();
            $network->delete();
          }
        }
        
       $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
      
      return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
    }
  }


  public function membersAction()
  {
    if( !$this->_helper->requireSubject('network') ) return;
    $network = Engine_Api::_()->core()->getSubject();

    // Multiple friend mode
    $select = $network->membership()->getMembersSelect();
    $this->view->members = $members = $paginator = Zend_Paginator::factory($select);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Get stuff
    $ids = array();
    foreach( $members as $member )
    {
      $ids[] = $member->user_id;
    }
    $this->view->memberIds = $ids;

    // Get the items
    $memberUsers = array();
    foreach( Engine_Api::_()->getItemTable('user')->find($ids) as $memberUser )
    {
      $memberUsers[$memberUser->getIdentity()] = $memberUser;
    }
    $this->view->memberUsers = $memberUsers;



  }
}