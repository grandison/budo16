<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PlaylistSong.php 8243 2011-01-18 03:55:31Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Model_PlaylistSong extends Core_Model_Item_Abstract
{
  public function getShortType()
  {
    return 'song'; // helps core_model_item get to primary key "song_id" (it adds "_id")
  }
  
  public function getTitle()
  {
    if( !empty($this->title) ) {
      return $this->title;
    } else {
      $translate = Zend_Registry::get('Zend_Translate');
      return $translate->translate('Untitled Song');
    }
  }

  public function setTitle($newTitle)
  {
    $this->title = $newTitle;
    $this->save();
    return $this;
  }

  public function getFilePath()
  {
    $file = Engine_Api::_()->getItem('storage_file', $this->file_id);
    if( $file ) {
      return $file->map();
    }
  }
  
  public function getHref($params = array())
  {
    return $this->getParent()->getHref($params);
  }

  public function getParent()
  {
    return Engine_Api::_()->getItem('music_playlist', $this->playlist_id);
  }

  public function getRichContent($view = false, $params = array())
  {
    $playlist      = $this->getParent();
    $videoEmbedded = '';

    // $view == false means that this rich content is requested from the activity feed
    if( $view == false ){
      $desc   = strip_tags($playlist->description);
      $desc   = "<div class='music_desc'>".(Engine_String::strlen($desc) > 255 ? Engine_String::substr($desc, 0, 255) . '...' : $desc)."</div>";
      $zview  = Zend_Registry::get('Zend_View');
      $zview->playlist     = $playlist;
      $zview->songs        = array($this);
      $zview->short_player = true;
      $videoEmbedded       = $desc . $zview->render('application/modules/Music/views/scripts/_Player.tpl');
    }

    return $videoEmbedded;
  }

  /**
   * Returns languagified play count
   */
  public function playCountLanguagified()
  {
    return vsprintf(Zend_Registry::get('Zend_Translate')->_(array('%s play', '%s play', $this->play_count)),
                  Zend_Locale_Format::toNumber($this->play_count)                  
                  );
  }

  /**
   * Deletes songs from the Storage engine if no other playlists are
   * using the file, and from the playlist
   *
   * @return null
   */
  public function deleteUnused()
  {
    $file   = Engine_Api::_()->getItem('storage_file', $this->file_id);
    if ($file) {
      $table = Engine_Api::_()->getDbtable('playlistSongs', 'music');
      $count = $table->select()
                      ->from($table->info('name'), 'count(*) as count')
                      ->where('file_id = ?', $file->getIdentity())
                      ->query()
                      ->fetchColumn(0);
      if( $count <= 1 ) {
        try {
          $file->remove();
        } catch( Exception $e ) {
          
        }
      }
    }
    $this->delete();
  }
}