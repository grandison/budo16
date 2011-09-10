<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PlaylistSongs.php 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Model_DbTable_PlaylistSongs extends Engine_Db_Table
{
  protected $_name     = 'music_playlist_songs';
  //protected $_primary  = array('playlist_id', 'file_id');
  protected $_primary  = 'song_id';
  
  protected $_rowClass = 'Music_Model_PlaylistSong';
}