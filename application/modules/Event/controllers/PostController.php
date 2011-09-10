<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PostController.php 8213 2011-01-14 01:10:36Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_PostController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( Engine_Api::_()->core()->hasSubject() ) return;

    if( 0 !== ($post_id = (int) $this->_getParam('post_id')) &&
        null !== ($post = Engine_Api::_()->getItem('event_post', $post_id)) )
    {
      Engine_Api::_()->core()->setSubject($post);
    }
    
    $this->_helper->requireUser->addActionRequires(array(
      'edit',
      'delete',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
      'edit' => 'event_post',
      'delete' => 'event_post',
    ));
  }
  
  public function editAction()
  {
    $post = Engine_Api::_()->core()->getSubject('event_post');
    $event = $post->getParent('event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$event->isOwner($viewer) && !$post->isOwner($viewer) ) {
      if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid() ) {
        return;
      }
    }

    $this->view->form = $form = new Event_Form_Post_Edit();

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
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
    ));

  }

  public function deleteAction()
  {
    $post = Engine_Api::_()->core()->getSubject('event_post');
    $event = $post->getParent('event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$event->isOwner($viewer) && !$post->isOwner($viewer) ) {
      if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid() ) {
        return;
      }
    }

    $this->view->form = $form = new Event_Form_Post_Delete();

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

    try
    {

      $topic_id = $post->topic_id;
      $post->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Try to get topic
    $topic = Engine_Api::_()->getItem('event_topic', $topic_id);
    $href = ( null === $topic ? $event->getHref() : $topic->getHref() );
    return $this->_forward('success', 'utility', 'core', array(
      'closeSmoothbox' => true,
      'parentRedirect' => $href,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
    ));
  }
}