<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8427 2011-02-09 23:11:24Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Widget_CommentsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Get subject
    $subject = null;
    if( Engine_Api::_()->core()->hasSubject() ) {
      $subject = Engine_Api::_()->core()->getSubject();
    } else if( ($subject = $this->_getParam('subject')) ) {
      list($type, $id) = explode('_', $subject);
      $subject = Engine_Api::_()->getItem($type, $id);
    } else if( ($type = $this->_getParam('type')) &&
        ($id = $this->_getParam('id')) ) {
      $subject = Engine_Api::_()->getItem($type, $id);
    }

    if( !($subject instanceof Core_Model_Item_Abstract) ||
        !$subject->getIdentity() ||
        (!method_exists($subject, 'comments') && !method_exists($subject, 'likes')) ) {
      return $this->setNoRender();
    }
    
    // Perms
    $viewer = Engine_Api::_()->user()->getViewer();
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
      //$form->setAction($this->view->url(array('action' => '')))
      $form->populate(array(
        'identity' => $subject->getIdentity(),
        'type' => $subject->getType(),
      ));
    }
  }
}