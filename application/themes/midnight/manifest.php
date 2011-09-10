<?php
/**
 * SocialEngine
 *
 * @category   Application_Theme
 * @package    Midnight
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8885 2011-04-13 21:22:22Z jung $
 * @author     Alex
 */

return array(
  'package' => array(
    'type' => 'theme',
    'name' => 'midnight',
    'version' => '4.1.4',
    'revision' => '$Revision: 8885 $',
    'path' => 'application/themes/midnight',
    'repository' => 'socialengine.net',
    'title' => 'Midnight Theme',
    'thumb' => 'midnight_theme.jpg',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.1.4' => array(
        'mainfest.php' => 'Incremented version',
        'mobile.css' => 'Added new type of stylesheet',
      ),
      '4.0.1' => array(
        'manifest.php' => 'Incremented version; removed deprecated meta key',
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
      'application/themes/midnight',
    ),
  ),
  'files' => array(
    'theme.css',
    'constants.css',
  ),
  'nophoto' => array(
    'user' => array(
      'thumb_icon' => 'application/themes/midnight/images/nophoto_user_thumb_icon.png',
      'thumb_profile' => 'application/themes/midnight/images/nophoto_user_thumb_profile.png',
    ),
    'group' => array(
      'thumb_normal' => 'application/themes/midnight/images/nophoto_event_thumb_normal.jpg',
      'thumb_profile' => 'application/themes/midnight/images/nophoto_event_thumb_profile.jpg',
    ),
    'event' => array(
      'thumb_normal' => 'application/themes/midnight/images/nophoto_event_thumb_normal.jpg',
      'thumb_profile' => 'application/themes/midnight/images/nophoto_event_thumb_profile.jpg',
    ),
  ),
) ?>