<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: BlockController.php 7592 2010-10-06 23:13:03Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_BlockController extends Core_Controller_Action_User
{
  public function init()
  {
    $this->_helper->requireUser();
  }
  
  public function addAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', null);
    if( null == $user_id )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No member specified');
      return;
    }

    // Make form
    $this->view->form = $form = new User_Form_Block_Add();

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('block', 'user')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $user = $this->_helper->api()->user()->getUser($user_id);
      
      $viewer->addBlock($user);
      if($user->membership()->isMember($viewer, null))$user->membership()->removeMember($viewer);

      $db->commit();

      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Member blocked');
      
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Member blocked'))
      ));
    }
    catch( Exception $e )
    {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      $this->view->exception = $e->__toString();
    }
  }

  public function removeAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', null);
    if( null == $user_id )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('No member specified');
      return;
    }

    // Make form
    $this->view->form = $form = new User_Form_Block_Remove();

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('block', 'user')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = $this->_helper->api()->user()->getViewer();
      $user = $this->_helper->api()->user()->getUser($user_id);

      $viewer->removeBlock($user);

      $db->commit();

      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Member unblocked');

      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Member unblocked'))
      ));
    }
    catch( Exception $e )
    {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      $this->view->exception = $e->__toString();
    }
  }
  
  public function successAction()
  {
    // This is a smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->messages = $this->_getParam('messages', array());
  }
}