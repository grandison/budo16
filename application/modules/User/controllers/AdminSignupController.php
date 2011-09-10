<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSignupController.php 8646 2011-03-18 19:49:11Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_AdminSignupController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {    
    $signup_id = $this->_getParam('signup_id');

    // Prepare all steps
    $table = $this->_helper->api()->getDbtable('signup', 'user');
    $select = $table->select()
      ->order('order ASC');
    $this->view->steps = $steps = $table->fetchAll($select);

    // Get current step
    $currentStep = null;
    foreach( $steps as $step ) {
      if( $step->signup_id == $signup_id ) {
        $currentStep = $step;
      }
    }
    if( !$currentStep ) {
      $currentStep = $steps->offsetGet(0);
      $signup_id = $currentStep->signup_id;
    }
    $this->view->current_step = $currentStep;

    // Get form and view script
    $plugin = new $currentStep->class;
    $this->view->script = $plugin->getAdminScript();
    $this->view->form = $form = $plugin->getAdminForm();
    
    $form->setAction(Zend_Controller_Front::getInstance()
         ->getRouter()->assemble(
         array('module' => 'user', 'controller' => 'signup', 'signup_id' => $signup_id), 
         'admin_default', true));
    
    // Check method
    if( !$this->getRequest()->isPost() )  {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $plugin->onAdminProcess($form);
  }

  public function enableAction()
  {

  }

  public function disableAction()
  {

  }

  public function orderAction()
  {
    $table = $this->_helper->api()->getDbtable('signup', 'user');

    if( !$this->getRequest()->isPost() ) {
      return;
    }


    // Process
    $params = $this->getRequest()->getParams();
    $steps = $table->fetchAll($table->select());

    foreach( $steps as $step ) {
      $step->order = $this->getRequest()->getParam('step_' . $step->signup_id);
      $step->save();
    }
  }
}