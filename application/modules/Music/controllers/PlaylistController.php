<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PlaylistController.php 8221 2011-01-15 00:24:02Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_PlaylistController extends Core_Controller_Action_Standard
{
  public function init()
  {
    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'view')->isValid()) {
      return;
    }

    // Get viewer info
    $this->view->viewer     = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id  = Engine_Api::_()->user()->getViewer()->getIdentity();

    // Get subject
    if( null !== ($playlist_id = $this->_getParam('playlist_id')) &&
        null !== ($playlist = Engine_Api::_()->getItem('music_playlist', $playlist_id)) &&
        $playlist instanceof Music_Model_Playlist &&
        !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->core()->setSubject($playlist);
    }
  }

  public function viewAction()
  {
    // Set layout
    if( $this->_getParam('popout') ) {
      $this->view->popout = true;
      $this->_helper->layout->setLayout('default-simple');
    }

    // Check subject
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }

    // Get viewer/subject
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('music_playlist');

    // Increment view count
    if( !$viewer->isSelf($playlist->getOwner()) ) {
      $playlist->view_count++;
      //$playlist->play_count++;
      $playlist->save();
    }

    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_view = false;
    if( null !== ($message_id = $this->_getParam('message')) ) {
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      $message_view = $conversation->hasRecipient($viewer);
    }
    $this->view->message_view = $message_view;

    // Check auth
    if( !$message_view && !$this->_helper->requireAuth()->setAuthParams($playlist, $viewer, 'view')->isValid() ) {
      return;
    }
  }

  public function editAction()
  {
    // catch uploads from FLASH fancy-uploader and redirect to uploadSongAction()
    if( $this->getRequest()->getQuery('ul', false) ) {
      return $this->_forward('add-song', null, null, array('format' => 'json'));
    }

    // only members can upload music
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject('music_playlist')->isValid() ) {
      return;
    }

    // Get playlist
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('music_playlist');

    // only user and admins and moderators can edit
    if( !$this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid() ) {
      return;
    }
    
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_main', array(), 'music_main_manage');

    // Make form
    $this->view->form = $form = new Music_Form_Edit();
    $form->populate($playlist);

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    try {
      $form->saveValues();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }

    return $this->_helper->redirector->gotoUrl($playlist->getHref(), array('prependBase' => false));
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $playlist = Engine_Api::_()->getItem('music_playlist', $this->getRequest()->getParam('playlist_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($playlist, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    $this->view->form = $form = new Music_Form_Delete();

    if( !$playlist )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Playlist doesn't exists or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $playlist->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      foreach( $playlist->getSongs() as $song ) {
        $song->deleteUnused();
      }
      $playlist->delete();
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected playlist has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'music_general', true),
      'messages' => Array($this->view->message)
    ));
  }
  
  public function setProfileAction()
  {
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$this->_helper->requireSubject('music_playlist')->isValid() ) {
      return;
    }

    // Get playlist
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('music_playlist');
    $this->view->playlist_id = $playlist_id = $playlist->getIdentity();

    // Check owner
    if( $playlist->owner_id != Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    
    try {
      $playlist->setProfile();

      $this->view->success = true;
      $this->view->enabled = $playlist->profile;

      $db->commit();
    } catch( Exception $e ) {
      $this->view->success = false;
      
      $db->rollback();
    }
  }

  public function sortAction()
  {
    if( !$this->_helper->requireSubject('music_playlist')->isValid() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }

    // Get playlist
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('music_playlist');
    $this->view->playlist_id = $playlist_id = $playlist->getIdentity();

    // only user and admins and moderators can edit
    if( !$this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Not allowed to edit this playlist');
      return;
    }
    

    $songs = $playlist->getSongs();
    $order = explode(',', $this->getRequest()->getParam('order'));
    foreach( $order as $i => $item ) {
      $song_id = substr($item, strrpos($item, '_')+1);
      foreach( $songs as $song ) {
        if( $song->song_id == $song_id ) {
          $song->order = $i;
          $song->save();
        }
      }
    }
    
    $this->view->songs = $playlist->getSongs()->toArray();
  }




  
  public function addSongAction()
  {
    // Check user
    if( !$this->_helper->requireUser()->isValid() ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('You must be logged in.');
      return;
    }

    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'create')->checkRequire() ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('You are not allowed to upload songs.');
      return;
    }
    
    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    $playlistTable = Engine_Api::_()->getDbTable('playlists', 'music');

    // Get special playlist
    if( 0 >= ($playlist_id = $this->_getParam('playlist_id')) &&
        false != ($type = $this->_getParam('type')) ) {
      $playlist = $playlistTable->getSpecialPlaylist($viewer, $type);
      Engine_Api::_()->core()->setSubject($playlist);
    }

    // Check subject
    if( !$this->_helper->requireSubject('music_playlist')->checkRequire() ) {
      $this->view->success = false;
      $this->view->error   = $this->view->translate('Invalid playlist');
      return;
    }

    // Get playlist
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('music_playlist');
    $this->view->playlist_id = $playlist_id = $playlist->getIdentity();
    
    // check auth
    if( !$this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('You are not allowed to edit this playlist');
      return;
    }
    
    // Check file
    $values = $this->getRequest()->getPost();
    if( empty($values['Filename']) || empty($_FILES['Filedata']) ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('No file');
      return;
    }


    // Process
    $db = Engine_Api::_()->getDbtable('playlists', 'music')->getAdapter();
    $db->beginTransaction();

    try {

      // Create song
      $file = Engine_Api::_()->getApi('core', 'music')->createSong($_FILES['Filedata']);
      if( !$file ) {
        throw new Music_Model_Exception('Song was not successfully attached');
      }

      // Add song
      $song = $playlist->addSong($file);
      if( !$song ) {
        throw new Music_Model_Exception('Song was not successfully attached');
      }

      // Response
      $this->view->status = true;
      $this->view->song = $song;
      $this->view->song_id = $song->getIdentity();
      $this->view->song_url = $song->getFilePath();
      $this->view->song_title = $song->getTitle();
      
      $db->commit();

    } catch( Music_Model_Exception $e ) {
      $db->rollback();

      $this->view->status = false;
      $this->view->message = $this->view->translate($e->getMessage());
      return;
      
    } catch( Exception $e ) {
      $db->rollback();

      $this->view->status  = false;
      $this->view->message = $this->view->translate('Upload failed by database query');

      throw $e;
    }
  }
}