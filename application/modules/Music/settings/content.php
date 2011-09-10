<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Home Playlist',
    'description' => 'Displays a single selected playlist.',
    'category' => 'Music',
    'type' => 'widget',
    'name' => 'music.home-playlist',
    'autoEdit' => true,
    //'adminForm' => 'Music_Form_Admin_Widget_HomePlaylist',
    'defaultParams' => array(
      'title' => 'Playlist',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Profile Music',
    'description' => 'Displays a member\'s music on their profile.',
    'category' => 'Music',
    'type' => 'widget',
    'name' => 'music.profile-music',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Music',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Profile Player',
    'description' => 'Displays a flash player that plays the music the member has selected to play on their profile.',
    'category' => 'Music',
    'type' => 'widget',
    'name' => 'music.profile-player',
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Popular Playlists',
    'description' => 'Displays a list of popular playlists.',
    'category' => 'Music',
    'type' => 'widget',
    'name' => 'music.list-popular-playlists',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Playlists',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Radio',
          'popularType',
          array(
            'label' => 'Popular Type',
            'multiOptions' => array(
              'play' => 'Plays',
              'view' => 'Views',
              'comment' => 'Comments',
            ),
            'value' => 'play',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Playlists',
    'description' => 'Displays a list of recent playlists.',
    'category' => 'Music',
    'type' => 'widget',
    'name' => 'music.list-recent-playlists',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Playlists',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Radio',
          'recentType',
          array(
            'label' => 'Recent Type',
            'multiOptions' => array(
              'creation' => 'Creation Date',
              'modified' => 'Modified Date',
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
) ?>