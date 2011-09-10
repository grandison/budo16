<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'chat',
    'version' => '4.1.2',
    'revision' => '$Revision: 8441 $',
    'path' => 'application/modules/Chat',
    'repository' => 'socialengine.net',
    'title' => 'Chat',
    'description' => 'Chat',
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
      'path' => 'application/modules/Chat/settings/install.php',
      'class' => 'Chat_Installer',
    ),
    'directories' => array(
      'application/modules/Chat',
    ),
    'files' => array(
      'application/languages/en/chat.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Chat_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutAdminDefault',
      'resource' => 'Chat_Plugin_Core',
    )
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(

  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(

  ),
); ?>