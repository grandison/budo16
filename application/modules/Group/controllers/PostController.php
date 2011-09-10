<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PostController.php 7953 2010-12-06 20:22:11Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_PostController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( Engine_Api::_()->core()->hasSubject() ) return;

    if( 0 !== ($post_id = (int) $this->_getParam('post_id')) &&
        null !== ($post = Engine_Api::_()->getItem('group_post', $post_id)) )
    {
      Engine_Api::_()->core()->setSubject($post);
    }

    else if( 0 !== ($topic_id = (int) $this->_getParam('topic_id')) &&
        null !== ($topic = Engine_Api::_()->getItem('group_topic', $topic_id)) )
    {
      Engine_Api::_()->core()->setSubject($topic);
    }
    
    $this->_helper->requireUser->addActionRequires(array(
      'edit',
      'delete',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
      'edit' => 'group_post',
      'delete' => 'group_post',
    ));
  }
  
  public function editAction()
  {
    $post = Engine_Api::_()->core()->getSubject('group_post');
    $group = $post->getParent('group');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$group->isOwner($viewer) && !$post->isOwner($viewer) && !$group->authorization()->isAllowed($user, 'topic.edit') )
    {
      return $this->_helper->requireAuth->forward();
    }

    $this->view->form = $form = new Group_Form_Post_Edit();

    if( !$this->getRequest()->isPost() )
    {
      $form->populate($post->toArray());
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $post->setFromArray($form->getValues());
      $post->modified_date = date('Y-m-d H:i:s');
      $post->save();
      
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Try to get topic
    return $this->_forward('success', 'utility', 'core', array(
      'closeSmoothbox' => true,
      'parentRefresh' => true,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('The changes to your post have been saved.')),
    ));
  }

  public function deleteAction()
  {
    $post = Engine_Api::_()->core()->getSubject('group_post');
    $group = $post->getParent('group');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$group->isOwner($viewer) && !$post->isOwner($viewer) && !$group->authorization()->isAllowed($user, 'topic.edit') )
    {
      return $this->_helper->requireAuth->forward();
    }

    $this->view->form = $form = new Group_Form_Post_Delete();

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    $topic_id = $post->topic_id;

    try
    {
      $post->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Try to get topic
    $topic = Engine_Api::_()->getItem('group_topic', $topic_id);
    $href = ( null === $topic ? $group->getHref() : $topic->getHref() );
    return $this->_forward('success', 'utility', 'core', array(
      'closeSmoothbox' => true,
      'parentRedirect' => $href,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
    ));
  }


  public function canEdit($user)
  {
    return $this->getParent()->getParent()->authorization()->isAllowed($user, 'edit') || $this->getParent()->getParent()->authorization()->isAllowed($user, 'topic.edit') || $this->isOwner($user);
  }


}