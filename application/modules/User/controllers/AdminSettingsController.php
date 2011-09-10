<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSettingsController.php 7939 2010-12-05 19:18:57Z char $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    return $this->_helper->redirector->gotoRoute(array('route'=>'admin_default','module'=>'authorization','controller'=>'level', 'action' => 'edit'));

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('user_admin_main', array(), 'user_admin_main_settings');
  }

  public function generalAction()
  {

  }

  public function friendsAction()
  {
    $form = new User_Form_Admin_Settings_Friends();
    $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $form->setMethod("POST");
    $this->view->form = $form;
    
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $form->saveValues();
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function facebookAction()
  {
    $form = $this->view->form = new User_Form_Admin_Facebook();
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      Engine_Api::_()->getApi('settings', 'core')->core_facebook = $form->getValues();
      $form->addNotice('Your changes have been saved.');
    }
    $form->populate(Engine_Api::_()->getApi('settings', 'core')->core_facebook);
  }

  public function levelAction()
  {
    return $this->_helper->redirector->gotoRoute(array('module'=>'authorization','controller'=>'level', 'action' => 'edit'));

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('user_admin_main', array(), 'user_admin_main_level');

    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;
    
    // Make form
    $this->view->form = $form = new User_Form_Admin_Settings_Level();
    $form->level_id->setValue($id);

    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');

    // Check post
    if( !$this->getRequest()->isPost() ) {
      $form->populate($permissionsTable->getAllowed('user', $id, array_keys($form->getValues())));
      return;
    }

    // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process

    $values = $form->getValues();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Set permissions
      $permissionsTable->setAllowed('user', $id, $values);

      // Update search
      $userTable = Engine_Api::_()->getItemTable('user');
      $userTable->update(array(
        'search' => 1,
      ), array(
        'level_id = ?' => $id,
        'search = ?' => 0,
      ));

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    $form->addNotice('Your changes have been saved.');
  }
}
