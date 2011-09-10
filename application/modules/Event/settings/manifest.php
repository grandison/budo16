<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     Sami
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'event',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Event',
    'repository' => 'socialengine.net',
    'title' => 'Events',
    'description' => 'Events',
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
      'path' => 'application/modules/Event/settings/install.php',
      'class' => 'Event_Installer',
    ),
    'directories' => array(
      'application/modules/Event',
    ),
    'files' => array(
      'application/languages/en/event.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Event_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Event_Plugin_Core',
    ),
    array(
      'event' => 'getActivity',
      'resource' => 'Event_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Event_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'event',
    'event_album',
    'event_photo',
    'event_post',
    'event_topic',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'event_extended' => array(
      'route' => 'events/:controller/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'event_general' => array(
      'route' => 'events/:action/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|create|delete|list|manage|edit)',
      )
    ),
    'event_specific' => array(
      'route' => 'events/:action/:event_id/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'event',
        'action' => 'index',
      ),
      'reqs' => array(
        'action' => '(edit|delete|join|leave|invite|accept|style|reject)',
        'event_id' => '\d+',
      )
    ),
    'event_profile' => array(
      'route' => 'event/:id/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'profile',
        'action' => 'index',
      ),
      'reqs' => array(
        'id' => '\d+',
      )
    ),
    'event_upcoming' => array(
      'route' => 'events/upcoming/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
        'filter' => 'future'
      )
    ),
    'event_past' => array(
      'route' => 'events/past/*',
      'defaults' => array(
        'module' => 'event',
        'controller' => 'index',
        'action' => 'browse',
        'filter' => 'past'
      )
    ),
  )
) ?>