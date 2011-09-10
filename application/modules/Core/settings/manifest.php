<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8942 2011-05-16 21:40:10Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'core',
    'version' => '4.1.5',
    'revision' => '$Revision: 8942 $',
    'path' => 'application/modules/Core',
    'repository' => 'socialengine.net',
    'title' => 'Core',
    'description' => 'Core',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Core/settings/install.php',
      'class' => 'Core_Install',
      'priority' => 9001,
    ),
    'dependencies' => array(
      array(
        'type' => 'library',
        'name' => 'engine',
        'required' => true,
        'minVersion' => '4.1.2',
      ),
    ),
    'directories' => array(
      'application/modules/Core',
    ),
    'files' => array(
      'application/languages/en/core.csv',
    ),
    'tests' => array(
      // MySQL Adapters
      array(
        'type' => 'Multi',
        'name' => 'MySQL',
        'allForOne' => true,
        'messages' => array(
          'allTestsFailed' => 'Requires one of the following extensions: mysql, mysqli, pdo_mysql',
        ),
        'tests' => array(
          array(
            'type' => 'PhpExtension',
            'extension' => 'mysql',
          ),
          array(
            'type' => 'PhpExtension',
            'extension' => 'mysqli',
          ),
          array(
            'type' => 'PhpExtension',
            'extension' => 'pdo_mysql',
          ),
        ),
      ),
      // MySQL Server
      array(
        'type' => 'MysqlServer',
        'name' => 'MySQL 4.1',
        'minVersion' => '4.1',
      ),
      array(
        'type' => 'MysqlEngine',
        'name' => 'MySQL InnoDB Storage Engine',
        'engine' => 'innodb',
      ),
    ),
  ),
  // Composer -------------------------------------------------------------------
  'composer' => array(
    'link' => array(
      'script' => array('_composeLink.tpl', 'core'),
      'plugin' => 'Core_Plugin_Composer',
      'auth' => array('core_link', 'create'),
    ),
    'tag' => array(
      'script' => array('_composeTag.tpl', 'core'),
      'plugin' => 'Core_Plugin_Composer',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Core_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'core_ad',
    'core_adcampaign',
    'core_adphoto',
    'core_comment',
    'core_geotag',
    'core_link',
    'core_like',
    'core_list',
    'core_list_item',
    'core_page',
    'core_report',
    'core_mail_template',
    'core_tag',
    'core_tag_map',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'home' => array(
      'route' => '/',
      'defaults' => array(
        'module' => 'core',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
    'core_home' => array(
      'route' => '/',
      'defaults' => array(
        'module' => 'core',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
    'confirm' => array(
      'route'=>'/confirm',
      'defaults' => array(
        'module'=>'core',
        'controller'=>'confirm',
        'action'=>'confirm'
      )
    ),
    
    // Admin - General
    'core_admin_settings' => array(
      'route' => "admin/core/settings/:action/*",
      'defaults' => array(
        'module' => 'core',
        'controller' => 'admin-settings',
        'action' => 'index'
      ),
      'reqs' => array(
        'action' => '\D+',
      )
    ),
  )
) ?>