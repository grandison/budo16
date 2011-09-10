<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'music',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Music',
    'repository' => 'socialengine.net',
    'title' => 'Music',
    'description' => 'Music',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '4.1.2',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       'enable',
       'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Music/settings/install.php',
      'class' => 'Music_Installer',
    ),
    'directories' => array(
      'application/modules/Music',
    ),
    'files' => array(
      'application/languages/en/music.csv',
    ),
  ),
  // Compose -------------------------------------------------------------------
  'compose' => array(
    array('_composeMusic.tpl', 'music'),
  ),
  'composer' => array(
    'music' => array(
      'script' => array('_composeMusic.tpl', 'music'),
      'plugin' => 'Music_Plugin_Composer',
      'auth' => array('music_playlist', 'create'),
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Music_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Music_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'music_playlist',
    'music_playlist_song',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'music_extended' => array(
      'route' => 'music/:controller/:action/*',
      'defaults' => array(
        'module' => 'music',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      ),
    ),
    'music_general' => array(
      'route' => 'music/:action/*',
      'defaults' => array(
        'module' => 'music',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|manage|create)',
      ),
    ),
    'music_playlist_view' => array(
      'route' => 'music/:playlist_id/:slug/*',
      'defaults' => array(
        'module' => 'music',
        'controller' => 'playlist',
        'action' => 'view',
        'slug' => '',
      ),
      'reqs' => array(
        'playlist_id' => '\d+'
      )
    ),
    'music_playlist_specific' => array(
      'route' => 'music/:playlist_id/:slug/:action/*',
      'defaults' => array(
        'module' => 'music',
        'controller' => 'playlist',
        'action' => 'view',
      ),
      'reqs' => array(
        'playlist_id' => '\d+',
        'action' => '(view|edit|delete|sort|set-profile|add-song|append-song)',
      ),
    ),
    'music_song_specific' => array(
      'route' => 'music/song/:song_id/:action/*',
      'defaults' => array(
        'module' => 'music',
        'controller' => 'song',
        'action' => 'view',
      ),
      'reqs' => array(
        'song_id' => '\d+',
        'action' => '(view|delete|rename|tally|upload|append)',
      ),
    ),
  ),
) ?>