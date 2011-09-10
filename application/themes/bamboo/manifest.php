<?php
/**
 * SocialEngine
 *
 * @category   Application_Theme
 * @package    Bamboo
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8885 2011-04-13 21:22:22Z jung $
 * @author     Bryan
 */

return array(
  'package' => array(
    'type' => 'theme',
    'name' => 'bamboo',
    'version' => '4.1.4',
    'revision' => '$Revision: 8885 $',
    'path' => 'application/themes/bamboo',
    'repository' => 'socialengine.net',
    'title' => 'Bamboo Theme',
    'thumb' => 'bamboo_theme.jpg',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.1.4' => array(
        'mainfest.php' => 'Incremented version',
        'mobile.css' => 'Added new type of stylesheet',
      ),
      '4.0.2' => array(
        'manifest.php' => 'Incremented version; removed deprecated meta key',
      ),
      '4.0.1' => array(
        'manifest.php' => 'Incremented version',
        'theme.css' => 'Uses fixed relative URL support in Scaffold',
      ),
    ),
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
      'remove',
    ),
    'callback' => array(
      'class' => 'Engine_Package_Installer_Theme',
    ),
    'directories' => array(
      'application/themes/bamboo',
    ),
  ),
  'files' => array(
    'theme.css',
    'constants.css',
  )
) ?>