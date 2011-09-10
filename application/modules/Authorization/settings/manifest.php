<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8586 2011-03-11 01:24:50Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'authorization',
    'version' => '4.1.3',
    'revision' => '$Revision: 8586 $',
    'path' => 'application/modules/Authorization',
    'repository' => 'socialengine.net',
    'title' => 'Authorization',
    'description' => 'Authorization',
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
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Authorization/settings/install.php',
      'class' => 'Authorization_Install',
      'priority' => 5000,
    ),
    'directories' => array(
      'application/modules/Authorization',
    ),
    'files' => array(
      'application/languages/en/authorization.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Authorization_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'authorization_level'
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'authorization_admin_levels' => array(
      'route' => '/admin/levels/:action/*',
      'defaults' => array(
        'module' => 'authorization',
        'controller' => 'admin-level',
        'action' => 'index',
        //'id' => 0
      )
    )
  )
) ?>