<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     Jung
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'video',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Video',
    'repository' => 'socialengine.net',
    'title' => 'Videos',
    'description' => 'Videos',
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
      'path' => 'application/modules/Video/settings/install.php',
      'class' => 'Video_Installer',
    ),
    'directories' => array(
      'application/modules/Video',
    ),
    'files' => array(
      'application/languages/en/video.csv',
    ),
  ),
  // Compose
  'composer' => array(
    'video' => array(
      'script' => array('_composeVideo.tpl', 'video'),
      'plugin' => 'Video_Plugin_Composer',
      'auth' => array('video', 'create'),
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'video',
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Video_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Video_Plugin_Core',
    ),
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'video_general' => array(
      'route' => 'videos/:action/*',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|create|list|manage)',
      )
    ),
    'video_profile' => array(
      'route' => 'video/:id/*',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'profile',
        'action' => 'index',
      ),
      'reqs' => array(
        'id' => '\d+',
      )
    ),
    'video_view' => array(
      'route' => 'videos/:user_id/:video_id/:slug/*',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'view',
        'slug' => '',
      ),
      'reqs' => array(
        'user_id' => '\d+'
      )
    ),
    'video_delete' => array(
      'route' => 'video/delete/:video_id',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'delete'
      )
    ),
    'video_edit' => array(
      'route' => 'video/edit/:video_id',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'edit'
      )
    ),
    'video_retry' => array(
      'route' => 'video/retry/:retry',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'create'
      )
    ),
  )
) ?>