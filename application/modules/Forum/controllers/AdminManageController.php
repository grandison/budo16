<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminManageController.php 8344 2011-01-29 07:46:14Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_AdminManageController extends Core_Controller_Action_Admin
{
  // @todo add in stricter settings for admin level checking
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('forum_admin_main', array(), 'forum_admin_main_manage');

    $table = Engine_Api::_()->getItemTable('forum_category');
    $this->view->categories = $table->fetchAll($table->select()->order('order ASC'));

  }

  public function moveForumAction()
  {
    if( $this->getRequest()->isPost() ) {
      $forum_id = $this->_getParam('forum_id');
      $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);
      $forum->moveUp();
    }
  }

  public function moveCategoryAction()
  {
    if( $this->getRequest()->isPost() ) {
      $category_id = $this->_getParam('category_id');
      $category = Engine_Api::_()->getItem('forum_category', $category_id);
      $category->moveUp();
    }
  }

  public function editForumAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Forum_Edit();

    $forum_id = $this->getRequest()->getParam('forum_id');
    $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);

    // Populate
    $form->populate($forum->toArray());
    $form->populate(array(
      'title' => htmlspecialchars_decode($forum->title),
      'description' => htmlspecialchars_decode($forum->description),
    ));

    $auth = Engine_Api::_()->authorization()->context;
    $allowed = array();
    if( $auth->isAllowed($forum, 'everyone', 'view') ) {
      
    } else {
      $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
      foreach( $levels as $level ) {
        if( Engine_Api::_()->authorization()->context->isAllowed($forum, $level, 'view') ) {
          $allowed[] = $level->getIdentity();
        }
      }
      if( count($allowed) == 0 || count($allowed) == count($levels) ) {
        $allowed = null;
      }
    }
    if( !empty($allowed) ) {
      $form->populate(array(
        'levels' => $allowed,
      ));
    }

    // Check request/method
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $table = Engine_Api::_()->getItemTable('forum_forum');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {

      if( $forum->category_id != $values['category_id'] ) {
        $forum->order = Engine_Api::_()->getItem('forum_category', $values['category_id'])->getHighestOrder() + 1;
      }

      $forum->setFromArray($values);
      $forum->title = htmlspecialchars($values['title']);
      $forum->description = htmlspecialchars($values['description']);
      
      $forum->save();
      
      // Handle permissions
      $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();

      // Clear permissions
      $auth->setAllowed($forum, 'everyone', 'view', false);
      foreach( $levels as $level ) {
        $auth->setAllowed($forum, $level, 'view', false);
      }

      // Add
      if( count($values['levels']) == 0 || count($values['levels']) == count($form->getElement('levels')->options) ) {
        $auth->setAllowed($forum, 'everyone', 'view', true);
      } else {
        foreach( $values['levels'] as $levelIdentity ) {
          $level = Engine_Api::_()->getItem('authorization_level', $levelIdentity);
          $auth->setAllowed($forum, $level, 'view', true);
        }
      }

      // Extra auth stuff
      $auth->setAllowed($forum, 'registered', 'topic.create', true);
      $auth->setAllowed($forum, 'registered', 'post.create', true);
      $auth->setAllowed($forum, 'registered', 'comment', true);

      // Make mod list now
      $list = $forum->getModeratorList();
      $auth->setAllowed($forum, $list, 'topic.edit', true);
      $auth->setAllowed($forum, $list, 'topic.delete', true);

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum saved.')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }

  public function editCategoryAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Category_Edit();
    
    $category_id = $this->getRequest()->getParam('category_id');
    $category = Engine_Api::_()->getItem('forum_category', $category_id);
    $form->title->setValue(htmlspecialchars_decode($category->title));

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $category->title = htmlspecialchars($form->getValue('title'));
    $category->save();

    return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category renamed.')),
            'layout' => 'default-simple',
            'parentRefresh' => true,
    ));
  }

  public function addCategoryAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Category_Create();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $table = Engine_Api::_()->getItemTable('forum_category');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $values = $form->getValues();
      $category = $table->createRow();
      $category->title = htmlspecialchars($values['title']);
      $category->order = Engine_Api::_()->forum()->getMaxCategoryOrder() + 1;
      $category->save();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category added.')),
            'layout' => 'default-simple',
            'parentRefresh' => true,
    ));
  }

  public function addForumAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Forum_Create();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $table = Engine_Api::_()->getItemTable('forum_forum');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $forum = $table->createRow();
      $forum->setFromArray($values);
      $forum->title = htmlspecialchars($values['title']);
      $forum->description = htmlspecialchars($values['description']);
      $forum->order = $forum->getCollection()->getHighestOrder() + 1;
      $forum->save();

      // Handle permissions
      $auth = Engine_Api::_()->authorization()->context;
      $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();

      // Clear permissions
      $auth->setAllowed($forum, 'everyone', 'view', false);
      foreach( $levels as $level ) {
        $auth->setAllowed($forum, $level, 'view', false);
      }

      // Add
      if( count($values['levels']) == 0 || count($values['levels']) == count($form->getElement('levels')->options) ) {
        $auth->setAllowed($forum, 'everyone', 'view', true);
      } else {
        foreach( $values['levels'] as $levelIdentity ) {
          $level = Engine_Api::_()->getItem('authorization_level', $levelIdentity);
          $auth->setAllowed($forum, $level, 'view', true);
        }
      }

      // Extra auth stuff
      $auth->setAllowed($forum, 'registered', 'topic.create', true);
      $auth->setAllowed($forum, 'registered', 'post.create', true);
      $auth->setAllowed($forum, 'registered', 'comment', true);

      // Make mod list now
      $list = $forum->getModeratorList();
      $auth->setAllowed($forum, $list, 'topic.edit', true);
      $auth->setAllowed($forum, $list, 'topic.delete', true);

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum added.')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));

  }

  public function addModeratorAction()
  {
    $forum_id = $this->getRequest()->getParam('forum_id');
    $this->view->forum = $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);

    $form = $this->view->form = new Forum_Form_Admin_Moderator_Create();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $values = $form->getValues();
    $user_id = $values['user_id'];
    
    $moderator = Engine_Api::_()->getItem('user', $user_id);
    
    $list = $forum->getModeratorList();
    $list->add($moderator);

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Moderator Added')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }

  public function userSearchAction()
  {
    $page = $this->getRequest()->getParam('page', 1);
    $username = $this->getRequest()->getParam('username');
    $table = $this->_helper->api()->getDbtable('users', 'user');
    $select = $table->select();
    if( !empty($username) ) {
      $select = $select->where('username LIKE ? || displayname LIKE ?', '%' . $username . '%');
    }
    $forum_id = $this->getRequest()->getParam('forum_id');
    $this->view->forum = $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    $this->view->paginator->setItemCountPerPage(20);
  }

  public function removeModeratorAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Moderator_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $user_id = $this->getRequest()->getParam('user_id');
    $user = Engine_Api::_()->getItem('user', $user_id);

    $forum_id = $this->getRequest()->getParam('forum_id');
    $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);
    $list = $forum->getModeratorList();
    $list->remove($user);
    return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Moderator Removed')),
            'layout' => 'default-simple',
            'parentRefresh' => true,
    ));
  }

  public function deleteCategoryAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Category_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $table = Engine_Api::_()->getItemTable('forum_category');
    $db = $table->getAdapter();
    $db->beginTransaction();
    $category_id = $this->getRequest()->getParam('category_id');
    try
    {
      $category = Engine_Api::_()->getItem('forum_category', $category_id);
      $category->delete();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category deleted.')),
            'layout' => 'default-simple',
            'parentRefresh' => true
    ));
  }

  public function deleteForumAction()
  {
    $form = $this->view->form = new Forum_Form_Admin_Forum_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $table = Engine_Api::_()->getItemTable('forum_forum');
    $db = $table->getAdapter();
    $db->beginTransaction();
    $forum_id = $this->getRequest()->getParam('forum_id');
    try
    {
      $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);
      $forum->delete();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum deleted.')),
            'layout' => 'default-simple',
            'parentRefresh' => true
    ));
  }
}