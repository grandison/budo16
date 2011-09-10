<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8586 2011-03-11 01:24:50Z john $
 * @author     Steve
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'invite',
    'version' => '4.1.3',
    'revision' => '$Revision: 8586 $',
    'path' => 'application/modules/Invite',
    'repository' => 'socialengine.net',
    'title' => 'Invite',
    'description' => 'Invite',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'version' => '4.1.2',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'class' => 'Engine_Package_Installer_Module',
    ),
    'directories' => array(
      'application/modules/Invite',
    ),
    'files' => array(
      'application/languages/en/invite.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onUserCreateAfter',
      'resource' => 'Invite_Plugin_Signup',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'invite'
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    // Public
    // User
    'invite' => array(
      'route' => 'invite',
      'defaults' => array(
        'module' => 'invite',
        'controller' => 'index',
        'action' => 'index'
      )
    ),

    // Admin
    'invite_admin_settings' => array(
      'route' => 'admin/invite/settings',
      'defaults' => array(
        'module' => 'invite',
        'controller' => 'admin',
        'action' => 'settings'
      )
    ),
    'invite_admin_stats' => array(
      'route' => 'admin/invite/stats',
      'defaults' => array(
        'module' => 'invite',
        'controller' => 'admin',
        'action' => 'stats'
      )
    ),
  // end routes
  ),
);