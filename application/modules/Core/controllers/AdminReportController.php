<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminReportController.php 8623 2011-03-16 23:50:05Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminReportController extends Core_Controller_Action_Admin
{
  
  public function init()
  {
    if( !defined('_ENGINE_ADMIN_NEUTER') || !_ENGINE_ADMIN_NEUTER ) {
      $this->_helper->requireUser();
    }
  }

  public function indexAction()
  {
    // Make form
    $this->view->formFilter = $formFilter = new Core_Form_Admin_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array();
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'report_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;

    // Get paginator
    $table = Engine_Api::_()->getItemTable('core_report');
    $select = $table->select()
      ->order($filterValues['order'] . ' ' . $filterValues['direction']);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage(2);
  }

  public function viewAction()
  {
    // first get the item and then redirect admin to the item page
    $this->view->id = $id = $this->_getParam('id', null);
    $report = Engine_Api::_()->getItem('core_report', $id);
    $item = Engine_Api::_()->getItem($report->subject_type, $report->subject_id);
    if( $item ) {
      $this->_redirectCustom($item->getHref());
    } else {
      $this->view->missing = true;
    }
  }
  
  public function deleteAction()
  {
    $this->view->id = $id = $this->_getParam('id', null);
    $report = Engine_Api::_()->getItem('core_report', $id);

    // Save values
    if( $this->getRequest()->isPost() )
    {
      $report->delete();
      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
      //$form->addMessage('Changes Saved!');
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
      foreach ($ids_array as $id){
        $report = Engine_Api::_()->getItem('core_report', $id);
        if( $report ) {
          $report->delete();
        }
      }

      //$announcement->delete();
      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

  }
}