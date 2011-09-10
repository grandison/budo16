<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: SongController.php 8221 2011-01-15 00:24:02Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_SongController extends Core_Controller_Action_Standard
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
    if( null !== ($song_id = $this->_getParam('song_id')) &&
        null !== ($song = Engine_Api::_()->getItem('music_playlist_song', $song_id)) &&
        $song instanceof Music_Model_PlaylistSong ) {
      Engine_Api::_()->core()->setSubject($song);
    }
  }
  
  public function renameAction()
  {
    // Check subject
    if( !Engine_Api::_()->core()->hasSubject('music_playlist_song') ) {
      $this->view->success = false;
      $this->view->error = $translate->_('Not a valid song');
      return;
    }
    
    // Check method
    if( !$this->getRequest()->isPost() ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid request method');
      return;
    }

    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();

    // Check song/playlist
    if( !$song || !$playlist ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }

    // Check auth
    if( !Engine_Api::_()->authorization()->isAllowed($playlist, null, 'edit') ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Not allowed to edit this playlist');
      return;
    }


    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    try {

      $song->setTitle( $this->_getParam('title') );

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();

      $this->view->success = false;
      $this->view->error   = $translate->_('Unknown database error');
      throw $e;
    }

    $this->view->success = true;
  }

  public function deleteAction()
  {
    // Check subject
    if( !Engine_Api::_()->core()->hasSubject('music_playlist_song') ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Not a valid song');
      return;
    }

    // Check method
    if( !$this->getRequest()->isPost() ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid request method');
      return;
    }

    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();

    // Check song/playlist
    if( !$song || !$playlist ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }

    // Check auth
    if( !Engine_Api::_()->authorization()->isAllowed($playlist, null, 'edit') ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Not allowed to edit this playlist');
      return;
    }

    // Get file
    $file = Engine_Api::_()->getItem('storage_file', $song->file_id);
    if( !$file ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }
    
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();
    
    try {
      $song->deleteUnused();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();

      $this->view->success = false;
      $this->view->error = $this->view->translate('Unknown database error');
      throw $e;
    }

    $this->view->success = true;
  }

  public function tallyAction()
  {
    // Check subject
    if( !Engine_Api::_()->core()->hasSubject('music_playlist_song') ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Not a valid song');
      return;
    }

    // Get song/playlist
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');
    $playlist = $song->getParent();

    // Check song
    if( !$song || !$playlist ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('invalid song_id');
      return;
    }


    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $song->play_count++;
      $song->save();

      $playlist->play_count++;
      $playlist->save();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      $this->view->success = false;
      return;
    }

    $this->view->success = true;
    $this->view->song = $song->toArray();
    $this->view->play_count = $song->playCountLanguagified();
  }

  public function appendAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'create')->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject('music_playlist_song')->isValid() ) {
      return;
    }

    // Set song
    $song = Engine_Api::_()->core()->getSubject('music_playlist_song');

    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Get form
    $this->view->form = $form = new Music_Form_Song_Append();

    // Populate form
    $songTable = $song->getTable();
    $playlistTable = Engine_Api::_()->getDbtable('playlists', 'music');
    $playlists = $playlistTable->select()
      ->from($playlistTable, array('playlist_id', 'title'))
      ->where('owner_type = ?', 'user')
      ->where('owner_id = ?', $viewer->getIdentity())
      ->query()
      ->fetchAll();
    foreach( $playlists as $playlist ) {
      if( $playlist['playlist_id'] != $song->playlist_id ) {
        $form->playlist_id->addMultiOption($playlist['playlist_id'], $playlist['title']);
      }
    }
    
    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Get values
    $values = $form->getValues();
    if( empty($values['playlist_id']) && empty($values['title']) ) {
      return $form->addError('Please enter a title or select a playlist.');
    }
    

    
    // Process
    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();

    try {

      // Existing playlist
      if( !empty($values['playlist_id']) ) {
        $playlist = Engine_Api::_()->getItem('music_playlist', $values['playlist_id']);

        // already exists in playlist
        $alreadyExists = $songTable->select()
          ->from($songTable, 'song_id')
          ->where('playlist_id = ?', $playlist->getIdentity())
          ->where('file_id = ?', $song->file_id)
          ->limit(1)
          ->query()
          ->fetchColumn()
          ;
        if( $alreadyExists ) {
          return $form->getElement('playlist_id')->addErrorMessage('This playlist already has this song.');
        }
      }

      // New playlist
      else {
        $playlist = $playlistTable->createRow();
        $playlist->title = trim($values['title']);
        $playlist->owner_type = 'user';
        $playlist->owner_id = $viewer->getIdentity();
        $playlist->search = 1;
        $playlist->save();

        // Add action and attachments
        $auth = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($playlist, 'registered', 'comment', true);
        foreach( array('everyone', 'registered', 'member') as $role ) {
          $auth->setAllowed($playlist, $role, 'view', true);
        }

        // Only create activity feed item if "search" is checked
        if( $playlist->search ) {
          $activity = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $activity->addActivity(Engine_Api::_()->user()->getViewer(),
              $playlist, 'music_playlist_new');
          if( $action ) {
            $activity->attachActivity($action, $playlist);
          }
        }
      }

      // Add song
      $playlist->addSong($song->file_id);

      // Response
      $this->view->success = true;
      $this->view->message = $this->view->translate('Your changes have been saved.');
      $this->view->playlist = $playlist;

      $db->commit();

    } catch( Music_Model_Exception $e ) {
      $this->view->success = false;
      $this->view->error = $this->view->translate($e->getMessage());
      $form->addError($e->getMessage());

      $db->rollback();

    } catch( Exception $e ) {
      $this->view->success = false;
      $db->rollback();
    }
  }

  public function uploadAction()
  {
    // only members can upload music
    if( !$this->_helper->requireUser()->checkRequire() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Max file size limit exceeded or session expired.');
      return;
    }

    // Check method
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid request method');
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
      $song = Engine_Api::_()->getApi('core', 'music')->createSong($_FILES['Filedata']);
      $this->view->status   = true;
      $this->view->song     = $song;
      $this->view->song_id  = $song->getIdentity();
      $this->view->song_url = $song->getHref();
      $db->commit();

    } catch( Music_Model_Exception $e ) {
      $db->rollback();

      $this->view->status = false;
      $this->view->message = $this->view->translate($e->getMessage());

    } catch( Exception $e ) {
      $db->rollback();

      $this->view->status  = false;
      $this->view->message = $this->view->translate('Upload failed by database query');
      
      throw $e;
    }
  }
}