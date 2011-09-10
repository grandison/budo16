<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'poll',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Poll',
    'repository' => 'socialengine.net',
    'title' => 'Polls',
    'description' => 'Polls',
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
      'path' => 'application/modules/Poll/settings/install.php',
      'class' => 'Poll_Installer',
    ),
    'directories' => array(
      'application/modules/Poll',
    ),
    'files' => array(
      'application/languages/en/poll.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Poll_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Poll_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'poll'
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'poll_extended' => array(
      'route' => 'polls/:controller/:action/*',
      'defaults' => array(
        'module' => 'poll',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      ),
    ),
    'poll_general' => array(
      'route' => 'polls/:action/*',
      'defaults' => array(
        'module' => 'poll',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|manage|create|vote)',
      ),
    ),
    'poll_specific' => array(
      'route' => 'polls/:action/:poll_id/*',
      'defaults' => array(
        'module' => 'poll',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'poll_id' => '\d+',
        'action' => '(delete|edit|close|vote)',
      ),
    ),
    'poll_view' => array(
      'route' => 'polls/view/:poll_id/:slug',
      'defaults' => array(
        'module' => 'poll',
        'controller' => 'index',
        'action' => 'view',
        'slug' => '',
      ),
      'reqs' => array(
        'poll_id' => '\d+'
      )
    ),
  ),
);
