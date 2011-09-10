<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8942 2011-05-16 21:40:10Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'user',
    'version' => '4.1.5',
    'revision' => '$Revision: 8942 $',
    'path' => 'application/modules/User',
    'repository' => 'socialengine.net',
    'title' => 'Members',
    'description' => 'Members',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '4.1.3',
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
      'priority' => 3000,
    ),
    'directories' => array(
      'application/modules/User',
    ),
    'files' => array(
      'application/languages/en/user.csv',
    ),
  ),
  // Compose -------------------------------------------------------------------
  'compose' => array(
    array('_composeFacebook.tpl', 'user'),
  ),
  'composer' => array(
    'user' => array(
      'script' => array('_composeFacebook.tpl', 'user'),
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'User_Plugin_Core',
    ),
    array(
      'event' => 'onUserCreateAfter',
      'resource' => 'User_Plugin_Core',
    ),
    array(
      'event' => 'getAdminNotifications',
      'resource' => 'User_Plugin_Core',
    )
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'user',
    'user_list',
    'user_list_item',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    // User - General
    'user_extended' => array(
      'route' => 'members/:controller/:action/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'index',
        'action' => 'index'
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'user_general' => array(
      'route' => 'members/:action/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'index',
        'action' => 'browse'
      ),
      'reqs' => array(
        'action' => '(home|browse)',
      )
    ),

    // User - Specific
    'user_profile' => array(
      'route' => 'profile/:id/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'profile',
        'action' => 'index'
      )
    ),
    
    'user_login' => array(
      //'type' => 'Zend_Controller_Router_Route_Static',
      'route' => '/login/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'auth',
        'action' => 'login'
      )
    ),
    'user_logout' => array(
      'type' => 'Zend_Controller_Router_Route_Static',
      'route' => '/logout',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'auth',
        'action' => 'logout'
      )
    ),
    'user_signup' => array(
      'route' => '/signup/:action/*',
      'defaults' => array(
        'module' => 'user',
        'controller' => 'signup',
        'action' => 'index'
      )
    ),
  )
); ?>