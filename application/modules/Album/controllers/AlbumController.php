<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AlbumController.php 8381 2011-02-02 05:03:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_AlbumController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() ) return;
    
    if( 0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
        null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id)) )
    {
      Engine_Api::_()->core()->setSubject($photo);
    }

    else if( 0 !== ($album_id = (int) $this->_getParam('album_id')) &&
        null !== ($album = Engine_Api::_()->getItem('album', $album_id)) )
    {
      Engine_Api::_()->core()->setSubject($album);
    }
  }

  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('album')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('album_main');

    // Hack navigation
    foreach( $navigation->getPages() as $page )
    {
      if( $page->route != 'album_general' || $page->action != 'manage' ) continue;
      $page->active = true;
    }

    // Prepare data
    $this->view->album = $album = Engine_Api::_()->core()->getSubject();

    // Make form
    $this->view->form = $form = new Album_Form_Album_Edit();
    
    if( !$this->getRequest()->isPost() )
    {
      $form->populate($album->toArray());
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach( $roles as $role ) {
        if( 1 === $auth->isAllowed($album, $role, 'view') ) {
          $form->auth_view->setValue($role);
        }
        if( 1 === $auth->isAllowed($album, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
        if( 1 === $auth->isAllowed($album, $role, 'tag') ) {
          $form->auth_tag->setValue($role);
        }
      }

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    } 

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Process
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();
      $album->setFromArray($values);
      $album->save();

      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = key($form->auth_view->options);
        if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
        }
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = key($form->auth_comment->options);
        if( empty($values['auth_comment']) ) {
          $values['auth_comment'] = 'owner_member';
        }
      }
      if( empty($values['auth_tag']) ) {
        $values['auth_tag'] = key($form->auth_tag->options);
        if( empty($values['auth_tag']) ) {
          $values['auth_tag'] = 'owner_member';
        }
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $tagMax = array_search($values['auth_tag'], $roles);
      
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($album, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($album, $role, 'tag', ($i <= $tagMax));
      }
      
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($album) as $action ) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'album_general', true);
  }

  public function viewAction()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( !$this->_helper->requireSubject('album')->isValid() ) return;

    $this->view->album = $album = Engine_Api::_()->core()->getSubject();
    if( !$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid() ) return;

    // Prepare params
    $this->view->page = $page = $this->_getParam('page');

    // Prepare data
    $this->view->paginator = $paginator = $album->getCollectiblesPaginator(); 
    $paginator->setItemCountPerPage($settings->getSetting('album_page', 25));
    $paginator->setCurrentPageNumber($page);

    // Do other stuff
    $this->view->mine = true;
    $this->view->canEdit = $this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->checkRequire();
    if( !$album->getOwner()->isSelf(Engine_Api::_()->user()->getViewer()) )
    {
      $album->view_count++;
      $album->save();
      $this->view->mine = false;
    }
  }
  
  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $album = Engine_Api::_()->getItem('album', $this->getRequest()->getParam('album_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $this->view->form = $form = new Album_Form_Album_Delete();

    if( !$album )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Album doesn't exists or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $album->delete();
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Album has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'album_general', true),
      'messages' => Array($this->view->message)
    ));
  }

  public function editphotosAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('album')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('album_main');

    // Hack navigation
    foreach( $navigation->getPages() as $page )
    {
      if( $page->route != 'album_general' || $page->action != 'manage' ) continue;
      $page->active = true;
    }

    // Prepare data
    $this->view->album = $album = Engine_Api::_()->core()->getSubject();

    $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage(10);

    // Make form
    $this->view->form = $form = new Album_Form_Album_Photos();
    
    foreach( $paginator as $photo ) {
      $subform = new Album_Form_Album_EditPhoto(array('elementsBelongTo' => $photo->getGuid()));
      $subform->populate($photo->toArray());
      $form->addSubForm($subform, $photo->getGuid());
      $form->cover->addMultiOption($photo->getIdentity(), $photo->getIdentity());
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $table = $album->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();
      if( !empty($values['cover']) ) {
        $album->photo_id = $values['cover'];
        $album->save();
      }


      // Process
      foreach( $paginator as $photo )
      {
        $subform = $form->getSubForm($photo->getGuid());
        $values = $subform->getValues();

        $values = $values[$photo->getGuid()];
        unset($values['photo_id']);
        if( isset($values['delete']) && $values['delete'] == '1' )
        {
          $photo->delete();
        }
        else
        {
          $photo->setFromArray($values);
          $photo->save();
        }
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_helper->redirector->gotoRoute(array('action' => 'view', 'album_id' => $album->album_id), 'album_specific', true);
  }

  public function orderAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject('album')->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

    $album = Engine_Api::_()->core()->getSubject();

    $order = $this->_getParam('order');
    if( !$order ) {
      $this->view->status = false;
      return;
    }
    
    // Get a list of all photos in this album, by order
    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $currentOrder = $photoTable->select()
      ->from($photoTable, 'photo_id')
      ->where('collection_id = ?', $album->getIdentity())
      ->order('order ASC')
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;

    // Find the starting point?
    $start = null;
    $end = null;
    for( $i = 0, $l = count($currentOrder); $i < $l; $i++ ) {
      if( in_array($currentOrder[$i], $order) ) {
        $start = $i;
        $end = $i + count($order);
        break;
      }
    }

    if( null === $start || null === $end ) {
      $this->view->status = false;
      return;
    }

    for( $i = 0, $l = count($currentOrder); $i < $l; $i++ ) {
      if( $i >= $start && $i <= $end ) {
        $photo_id = $order[$i - $start];
      } else {
        $photo_id = $currentOrder[$i];
      }
      $photoTable->update(array(
        'order' => $i,
      ), array(
        'photo_id = ?' => $photo_id,
      ));
    }

    $this->view->status = true;
  }


  public function composeUploadAction()
  {
    if( !Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
      $this->_redirect('login');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
      return;
    }

    if( empty($_FILES['Filedata']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Get album
    $viewer = Engine_Api::_()->user()->getViewer();
    $table = Engine_Api::_()->getDbtable('albums', 'album');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $type = $this->_getParam('type', 'wall');

      if (empty($type)) $type = 'wall';

      $album = $table->getSpecialAlbum($viewer, $type);

      $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'user',
        'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
      ));
      $photo->save();
      $photo->setPhoto($_FILES['Filedata']);
      
      if( $type == 'message' ) {
        $photo->title = Zend_Registry::get('Zend_Translate')->_('Attached Image');
      }
      $photo->collection_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id )
      {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      if( $type != 'message' ) {
        // Authorizations
        $auth      = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($photo, 'everyone', 'view',    true);
        $auth->setAllowed($photo, 'everyone', 'comment', true);
      }
      
      $db->commit();

      $this->view->status = true;
      $this->view->photo_id = $photo->photo_id;
      $this->view->album_id = $album->album_id;
      $this->view->src = $photo->getPhotoUrl();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Photo saved successfully');
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      $this->view->status = false;
    }
  }
}