<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'forum',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Forum',
    'repository' => 'socialengine.net',
    'title' => 'Forum',
    'description' => 'Forum',
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
      'path' => 'application/modules/Forum/settings/install.php',
      'class' => 'Forum_Installer',
    ),
    'directories' => array(
      'application/modules/Forum',
    ),
    'files' => array(
      'application/languages/en/forum.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Forum_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteAfter',
      'resource' => 'Forum_Plugin_Core'
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Forum_Plugin_Core'
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'forum', // Hack, forum_forum should be removed
    'forum_forum',
    'forum_category',
    'forum_container',
    'forum_post',
    'forum_signature',
    'forum_topic',
    'forum_list',
    'forum_list_item'
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'forum_general' => array(
      'route' => 'forums/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
    'forum_forum' => array(
      'route' => 'forums/:forum_id/:slug/:action/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'forum',
        'action' => 'view',
        'slug' => '-',
      ),
      'reqs' => array(
        'action' => '(create|edit|delete|view|topic-create)',
        'slug' => '[\w-]+',
      ),
    ),
    'forum_topic' => array(
      'route' => 'forums/topic/:topic_id/:slug/:action/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'topic',
        'action' => 'view',
        'slug' => '-',
      ),
      'reqs' => array(
        'action' => '(edit|delete|close|rename|move|sticky|view|watch|post-create)',
        'slug' => '[\w-]+',
      ),
    ),
    'forum_post' => array(
      'route' => 'forums/post/:post_id/:action/*',
      //'route' => 'forums/post/:post_id/:slug/:action/*',
      'defaults' => array(
        'module' => 'forum',
        'controller' => 'post',
        'action' => 'view',
        //'slug' => '-',
      ),
      'reqs' => array(
        'action' => '(edit|delete)',
        //'slug' => '[\w-]+',
      ),
    ),
  )
) ?>