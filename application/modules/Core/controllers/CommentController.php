<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: CommentController.php 8375 2011-02-02 02:09:25Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_CommentController extends Core_Controller_Action_Standard
{
  public function init()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $type = $this->_getParam('type');
    $identity = $this->_getParam('id');
    if( $type && $identity ) {
      $item = Engine_Api::_()->getItem($type, $identity);
      if( $item instanceof Core_Model_Item_Abstract && 
          (method_exists($item, 'comments') || method_exists($item, 'likes')) ) {
        if( !Engine_Api::_()->core()->hasSubject() ) {
          Engine_Api::_()->core()->setSubject($item);
        }
        //$this->_helper->requireAuth()->setAuthParams($item, $viewer, 'comment');
      }
    }

    //$this->_helper->requireUser();
    $this->_helper->requireSubject();
    //$this->_helper->requireAuth();
  }

  public function listAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    
    // Perms
    $this->view->canComment = $canComment = $subject->authorization()->isAllowed($viewer, 'comment');
    $this->view->canDelete = $subject->authorization()->isAllowed($viewer, 'edit');

    // Likes
    $this->view->viewAllLikes = $this->_getParam('viewAllLikes', false);
    $this->view->likes = $likes = $subject->likes()->getLikePaginator();

    // Comments

    // If has a page, display oldest to newest
    if( null !== ( $page = $this->_getParam('page')) )
    {
      $commentSelect = $subject->comments()->getCommentSelect();
      $commentSelect->order('comment_id ASC');
      $comments = Zend_Paginator::factory($commentSelect);
      $comments->setCurrentPageNumber($page);
      $comments->setItemCountPerPage(10);
      $this->view->comments = $comments;
      $this->view->page = $page;
    }

    // If not has a page, show the
    else
    {
      $commentSelect = $subject->comments()->getCommentSelect();
      $commentSelect->order('comment_id DESC');
      $comments = Zend_Paginator::factory($commentSelect);
      $comments->setCurrentPageNumber(1);
      $comments->setItemCountPerPage(4);
      $this->view->comments = $comments;
      $this->view->page = $page;
    }

    if( $viewer->getIdentity() && $canComment ) {
      $this->view->form = $form = new Core_Form_Comment_Create();
      $form->populate(array(
        'identity' => $subject->getIdentity(),
        'type' => $subject->getType(),
      ));
    }
  }
  
  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid() ) {
      return;
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    $this->view->form = $form = new Core_Form_Comment_Create();

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Invalid request method");;
      return;
    }

    if( !$form->isValid($this->_getAllParams()) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Invalid data");
      return;
    }

    // Process
    $db = $subject->comments()->getCommentTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->comments()->addComment($viewer, $form->getValue('body'));

      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      $subjectOwner = $subject->getOwner('user');
      
      // Activity
      $action = $activityApi->addActivity($viewer, $subject, 'comment_' . $subject->getType(), '', array(
        'owner' => $subjectOwner->getGuid(),
        'body' => $form->getValue('body')
      ));

      //$activityApi->attachActivity($action, $subject);
      
      // Notifications

      // Add notification for owner (if user and not viewer)
      $this->view->subject = $subject->getGuid();
      $this->view->owner = $subjectOwner->getGuid();
      if( $subjectOwner->getType() == 'user' && $subjectOwner->getIdentity() != $viewer->getIdentity() )
      {
        $notifyApi->addNotification($subjectOwner, $viewer, $subject, 'commented', array(
          'label' => $subject->getShortType()
        ));
      }

      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      $commentedUserNotifications = array();
      foreach( $subject->comments()->getAllCommentsUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() == $viewer->getIdentity() || $notifyUser->getIdentity() == $subjectOwner->getIdentity() ) continue;

        // Don't send a notification if the user both commented and liked this
        $commentedUserNotifications[] = $notifyUser->getIdentity();

        $notifyApi->addNotification($notifyUser, $viewer, $subject, 'commented_commented', array(
          'label' => $subject->getShortType()
        ));
      }
      
      // Add a notification for all users that liked
      // @todo we should probably limit this
      foreach( $subject->likes()->getAllLikesUsers() as $notifyUser )
      {
        // Skip viewer and owner
        if( $notifyUser->getIdentity() == $viewer->getIdentity() || $notifyUser->getIdentity() == $subjectOwner->getIdentity() ) continue;
        
        // Don't send a notification if the user both commented and liked this
        if( in_array($notifyUser->getIdentity(), $commentedUserNotifications) ) continue;
        
        $notifyApi->addNotification($notifyUser, $viewer, $subject, 'liked_commented', array(
          'label' => $subject->getShortType()
        ));
      }

      // Increment comment count
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = 'Comment added';
    $this->view->body = $this->view->action('list', 'comment', 'core', array(
      'type' => $this->_getParam('type'),
      'id' => $this->_getParam('id'),
      'format' => 'html',
      'page' => 1,
    ));
    $this->_helper->contextSwitch->initContext();
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Comment id
    $comment_id = $this->_getParam('comment_id');
    if( !$comment_id ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No comment');
      return;
    }

    // Comment
    $comment = $subject->comments()->getComment($comment_id);
    if( !$comment ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No comment or wrong parent');
      return;
    }

    // Authorization
    if( !$subject->authorization()->isAllowed($viewer, 'edit') &&
        ($comment->poster_type != $viewer->getType() ||
        $comment->poster_id != $viewer->getIdentity()) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not allowed');
      return;
    }

    // Method
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    // Process
    $db = $subject->comments()->getCommentTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->comments()->removeComment($comment_id);

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment deleted');
  }

  public function likeAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid() ) {
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $comment_id = $this->_getParam('comment_id');

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    if( $comment_id ) {
      $commentedItem = $subject->comments()->getComment($comment_id);
    } else {
      $commentedItem = $subject;
    }
    
    // Process
    $db = $commentedItem->likes()->getAdapter();
    $db->beginTransaction();

    try {
      
      $commentedItem->likes()->addLike($viewer);
      
      // Add notification
      $owner = $commentedItem->getOwner();
      $this->view->owner = $owner->getGuid();
      if( $owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() ) {
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $notifyApi->addNotification($owner, $viewer, $commentedItem, 'liked', array(
          'label' => $commentedItem->getShortType()
        ));
      }
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    // For comments, render the resource
    if( $subject->getType() == 'core_comment' ) {
      $type = $subject->resource_type;
      $id = $subject->resource_id;
      Engine_Api::_()->core()->clearSubject();
    } else {
      $type = $subject->getType();
      $id = $subject->getIdentity();
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Like added');
    $this->view->body = $this->view->action('list', 'comment', 'core', array(
      'type' => $type,
      'id' => $id,
      'format' => 'html',
      'page' => 1,
    ));
    $this->_helper->contextSwitch->initContext();
  }

  public function unlikeAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid() ) {
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $comment_id = $this->_getParam('comment_id');

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    if( $comment_id ) {
      $commentedItem = $subject->comments()->getComment($comment_id);
    } else {
      $commentedItem = $subject;
    }

    // Process
    $db = $commentedItem->likes()->getAdapter();
    $db->beginTransaction();

    try
    {
      $commentedItem->likes()->removeLike($viewer);

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    // For comments, render the resource
    if( $subject->getType() == 'core_comment' ) {
      $type = $subject->resource_type;
      $id = $subject->resource_id;
      Engine_Api::_()->core()->clearSubject();
    } else {
      $type = $subject->getType();
      $id = $subject->getIdentity();
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Like removed');
    $this->view->body = $this->view->action('list', 'comment', 'core', array(
      'type' => $type,
      'id' => $id,
      'format' => 'html',
      'page' => 1,
    ));
    $this->_helper->contextSwitch->initContext();
  }

  public function getLikesAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    $likes = $subject->likes()->getAllLikesUsers();
    $this->view->body = $this->view->translate(array('%s likes this', '%s like this',
      count($likes)), strip_tags($this->view->fluentList($likes)));
    $this->view->status = true;
  }
}