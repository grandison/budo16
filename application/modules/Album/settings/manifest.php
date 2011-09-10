<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     Jung
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'album',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Album',
    'repository' => 'socialengine.net',
    'title' => 'Albums',
    'description' => 'Albums',
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
      'path' => 'application/modules/Album/settings/install.php',
      'class' => 'Album_Installer',
    ),
    'directories' => array(
      'application/modules/Album',
    ),
    'files' => array(
      'application/languages/en/album.csv',
    ),
  ),
  // Compose -------------------------------------------------------------------
  'composer' => array(
    'photo' => array(
      'script' => array('_composePhoto.tpl', 'album'),
      'plugin' => 'Album_Plugin_Composer',
      'auth' => array('album', 'create'),
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'album',
    'album_photo',
    'photo'
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Album_Plugin_Core'
    ),
    array(
      'event' => 'onUserProfilePhotoUpload',
      'resource' => 'Album_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteAfter',
      'resource' => 'Album_Plugin_Core'
    )
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
     'album_extended' => array(
      'route' => 'albums/:controller/:action/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'index',
        'action' => 'index'
      ),
    ),
    'album_specific' => array(
      'route' => 'albums/:action/:album_id/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'album',
        'action' => 'view'
      ),
      'reqs' => array(
        'action' => '(compose-upload|delete|edit|editphotos|upload|view|order)',
      ),
    ),
    'album_general' => array(
      'route' => 'albums/:action/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'index',
        'action' => 'browse'
      ),
      'reqs' => array(
        'action' => '(browse|create|list|manage|upload|upload-photo)',
      ),
    ),

    'album_photo_specific' => array(
      'route' => 'albums/photos/:action/:album_id/:photo_id/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'photo',
        'action' => 'view'
      ),
      'reqs' => array(
        'action' => '(view)',
      ),
    ),
  ),
) ?>