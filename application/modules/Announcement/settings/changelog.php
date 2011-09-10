<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8825 2011-04-09 01:34:20Z john $
 * @author     John
 */
return array(
  '4.1.4' => array(
    'externals/styles/main.css' => 'Removed constants include',
    'externals/styles/mobile.css' => 'Added',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.2' => array(
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added preliminary layout enhancements',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.1' => array(
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.0' => array(
    'Model/Announcement.php' => 'Fixed incorrect getHref() method',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added pagination/item count limits to widgets',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'widgets/list-announcements/Controller.php' => 'Added pagination/item count limit',
  ),
  '4.0.3' => array(
    'Model/Announcement.php' => 'Removed redundant code',
    'settings/changelog.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.0.2' => array(
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    '/application/languages/en/announcement.csv' => 'Added phrases',
  ),
  '4.0.1' => array(
    'settings/manifest.php' => 'Incremented version',
    'widgets/list-announcements/index.tpl' => 'Switched array to paginator',
  ),
) ?>