<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ReportController.php 8185 2011-01-10 23:29:00Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_ReportController extends Core_Controller_Action_Standard
{
  public function init()
  {
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
  }

  public function createAction()
  {
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();

    $this->view->form = $form = new Core_Form_Report();
    $form->populate($this->_getAllParams());

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('core_report');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      
      $report = $table->createRow();
      $report->setFromArray(array_merge($form->getValues(), array(
        'subject_type' => $subject->getType(),
        'subject_id' => $subject->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      )));
      $report->save();

      // Increment report count
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.reports');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Close smoothbox
    $currentContext = $this->_helper->contextSwitch->getCurrentContext();
    if( null === $currentContext )
    {
      return $this->_helper->redirector->gotoRoute(array(), 'home', true);
    }
    else if( 'smoothbox' === $currentContext )
    {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => $this->view->translate('Your report has been submitted.'),
        'smoothboxClose' => true,
        'parentRefresh' => false,
      ));
    }
  }
}