<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PhotoController.php 8377 2011-02-02 04:10:01Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_PhotoController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() ) return;
    
    if( 0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
        null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id)) )
    {
      Engine_Api::_()->core()->setSubject($photo);
    }

    /*
    else if( 0 !== ($album_id = (int) $this->_getParam('album_id')) &&
        null !== ($album = Engine_Api::_()->getItem('album', $album_id)) )
    {
      Engine_Api::_()->core()->setSubject($album);
    }
     */
  }
   
  public function viewAction()
  {
    if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photo = $photo = Engine_Api::_()->core()->getSubject();
    $this->view->album = $album = $photo->getCollection();

    if( !$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer) ) {
      $photo->view_count = new Zend_Db_Expr('view_count + 1');
      $photo->save();
    }

    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_id = $this->getRequest()->getParam('message');
    $message_view = false;
    if ($message_id){
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) $message_view = true;
    }
    $this->view->message_view = $message_view;

    //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) return;
    if(!$message_view && !$this->_helper->requireAuth()->setAuthParams($photo, null, 'view')->isValid() ) return;

    $checkAlbum = Engine_Api::_()->getItem('album', $this->_getParam('album_id'));
    if( !($checkAlbum instanceof Core_Model_Item_Abstract) || !$checkAlbum->getIdentity() || $checkAlbum->album_id != $photo->collection_id )
    {
      $this->_forward('requiresubject', 'error', 'core');
      return;
    }

    $this->view->canEdit = $canEdit = $album->authorization()->isAllowed($viewer, 'edit');
    $this->view->canDelete = $canDelete = $album->authorization()->isAllowed($viewer, 'delete');
    $this->view->canTag = $canTag = $album->authorization()->isAllowed($viewer, 'tag');
    $this->view->canUntagGlobal = $canUntag = $album->isOwner($viewer);
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid() ) return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('album_photo');
    $album = $photo->getParent();

    $this->view->form = $form = new Album_Form_Photo_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $photo->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
      'layout' => 'default-simple',
      'parentRedirect' => $album->getHref(),
    ));
  }

  public function editAction()
  {
    if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('album_photo');

    $this->view->form = $form = new Album_Form_Photo_Edit();

    $form->populate($photo->toArray());

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $photo->setFromArray($values);
      $photo->save();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }
}