<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Playlist.php 8243 2011-01-18 03:55:31Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Model_Playlist extends Core_Model_Item_Abstract
{
  // Interfaces

  public function getTitle()
  {
    if( $this->special == 'wall' ) {
      return Zend_Registry::get('Zend_Translate')->_('_MUSIC_DEFAULT_PLAYLIST');
    } else if( $this->special == 'message' ) {
      return Zend_Registry::get('Zend_Translate')->_('_MUSIC_MESSAGE_PLAYLIST');
    } else if( !empty($this->title) ) {
      return $this->title;
    }
  }
  
  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    $slug = $this->getSlug();

    $params = array_merge(array(
      'route' => 'music_playlist_view',
      'reset' => true,
      //'user_id' => $this->owner_id,
      'playlist_id' => $this->playlist_id,
      'slug' => $slug,
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getRichContent($view = false, $params = array())
  {
    $videoEmbedded = '';

    // $view == false means that this rich content is requested from the activity feed
    if( !$view ) {
      $desc   = strip_tags($this->description);
      $desc   = "<div class='music_desc'>".(Engine_String::strlen($desc) > 255 ? Engine_String::substr($desc, 0, 255) . '...' : $desc)."</div>";
      $zview  = Zend_Registry::get('Zend_View');
      $zview->playlist     = $this;
      $zview->songs        = $this->getSongs();
      $zview->short_player = true;
      $zview->hideStats    = true;
      $videoEmbedded       = $desc . $zview->render('application/modules/Music/views/scripts/_Player.tpl');
    }

    // hide playlist if in production mode
    if (!count($zview->songs) && 'production' == APPLICATION_ENV) {
      throw new Exception('Empty playlists show not be shown');
    }
    
    return $videoEmbedded;
  }
  
  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   **/
  public function comments()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  public function getCommentCount()
  {
    return $this->comments()->getCommentCount();
  }

  public function getParent()
  {
    return $this->getOwner();
  }

  public function getSongs($file_id=null)
  {
    $table  = Engine_Api::_()->getDbtable('playlistSongs', 'music');
    $select = $table->select()
                    ->where('playlist_id = ?', $this->getIdentity())
                    ->order('order ASC');
    if (!empty($file_id))
      $select->where('file_id = ?', $file_id);

    return $table->fetchAll($select);
  }

  public function getSong($file_id) {
    return Engine_Api::_()->getDbtable('playlistSongs', 'music')->fetchRow(array(
      'playlist_id = ?' => $this->getIdentity(),
      'file_id = ?' => $file_id,
    ));
  }

  public function addSong($file_id)
  {
    if( $file_id instanceof Music_Model_PlaylistSong ) {
      $file_id = $file_id->file_id;
    }
    if( $file_id instanceof Storage_Model_File ) {
      $file = $file_id;
    } else {
      $file = Engine_Api::_()->getItem('storage_file', $file_id);
    }

    if( $file ) {
      $playlist_song = Engine_Api::_()->getDbtable('playlistSongs', 'music')->createRow();
      $playlist_song->playlist_id = $this->getIdentity();
      $playlist_song->file_id     = $file->getIdentity();
      $playlist_song->title       = preg_replace('/\.(mp3|m4a|aac|mp4)$/i', '', $file->name);
      $playlist_song->order       = count($this->getSongs());
      $playlist_song->save();
      return $playlist_song;
    }
    
    return false;
  }
  
  public function setProfile()
  {
    $table = Engine_Api::_()->getDbtable('playlists', 'music')->update(array(
      'profile' => 0,
    ), array(
      'owner_id' => $this->owner_id,
      'playlist_id != '.$this->getIdentity(),
    ));
    $this->profile = !$this->profile;
    $this->save();
  }
  
  public function setPhoto($photo)
  {
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
    } else if( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
    } else if( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
    } else {
      throw new Music_Model_Exception('Invalid argument passed to setPhoto: '.print_r($photo,1));
    }

    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => 'music_playlist',
      'parent_id' => $this->getIdentity()
    );

    // Save
    $storage = Engine_Api::_()->storage();

    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($path.'/m_'.$name)
      ->destroy();

    // Resize image (profile)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(200, 400)
      ->write($path.'/p_'.$name)
      ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
          ->resize(140, 160)
          ->write($path.'/in_'.$name)
          ->destroy();

    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x    = ($image->width - $size) / 2;
    $y    = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
          ->write($path.'/is_'.$name)
          ->destroy();

    // Store
    $iMain       = $storage->create($path.'/m_'.$name,  $params);
    $iProfile    = $storage->create($path.'/p_'.$name,  $params);
    $iIconNormal = $storage->create($path.'/in_'.$name, $params);
    $iSquare     = $storage->create($path.'/is_'.$name, $params);

    $iMain->bridge($iProfile,    'thumb.profile');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare,     'thumb.icon');

    // Remove temp files
    @unlink($path.'/p_'.$name);
    @unlink($path.'/m_'.$name);
    @unlink($path.'/in_'.$name);
    @unlink($path.'/is_'.$name);

    // Update row
    $this->modified_date = date('Y-m-d H:i:s');
    $this->photo_id      = $iMain->getIdentity();
    $this->save();

    return $this;
  }
  function isViewable()  { return $this->authorization()->isAllowed(null, 'view'); }
  function isEditable()  { return $this->authorization()->isAllowed(null, 'edit'); }
  function isDeletable() { return $this->authorization()->isAllowed(null, 'delete'); }
}