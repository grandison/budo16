<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8442 2011-02-11 01:14:59Z john $
 * @author     John
 */
return array(
  '4.1.2' => array(
    'controllers/AjaxController.php' => 'Fixed error on missing users',
    'controllers/IndexController.php' => 'Widget compatibility',
    'externals/scripts/core.js' => 'Fixed disconnection issue with IE8; fixed issue with system messages',
    'lite.php' => 'Added cache-control headers',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/index/index.tpl' => 'Widget compatibility',
    'widgets/chat/Controller.php' => 'Added',
    'widgets/chat/index.tpl' => 'Added',
  ),
  '4.1.1' => array(
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'externals/styles/main.css' => 'IE formatting fix',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.0' => array(
    '/application/languages/en/chat.csv' => 'Added phrases',
    'controllers/AdminSettingsController.php' => 'Removed global on/off settings',
    'controllers/AjaxController.php' => 'Rooms are ordered by title',
    'controllers/IndexController.php' => 'Fixes issue with level settings disabling chat',
    'externals/ding.mp3' => 'Added for IM sound, compliments http://soundbible.com/1127-Computer-Error.html',
    'externals/scripts/core.js' => 'Added IM sound; AJAX requests are sent through index.php now to address baseUrl issues with profile address; added translations for site-wide IM chat',
    'Form/Admin/Settings/Global.php' => 'Removed global on/off settings',
    'Model/DbTable/RoomUsers.php' => 'Fixes issue where IM and chat would not work if the mysql connection timezone could not be changed',
    'Model/DbTable/Users.php' => 'Fixes issue where IM and chat would not work if the mysql connection timezone could not be changed',
    'Model/Message.php' => 'Fixes issue with system messages when using mysql adapter',
    'Plugin/Core.php' => 'Added soundmanager for IM sound; fixes issue with level settings disabling chat',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.4-4.1.0.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/index/index.tpl' => 'Fixes issue with level settings disabling chat, IE not seeing list of rooms',
  ),
  '4.0.4' => array(
    'controllers/AdminManageController.php' => 'Added pagination',
    'externals/scripts/core.js' => 'Added missing translation',
    'Plugin/Core.php' => 'Added missing translation',
    'settings/changelog.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/index.tpl' => 'Added pagination',
  ),
  '4.0.3' => array(
    'controllers/AdminSettingsController.php' => 'Fixed warning message',
    'controllers/IndexController.php' => 'Removed deprecated code',
    'Bootstrap.php' => 'Removed deprecated code',
    'externals/scripts/core.js' => 'Improved language and localization support',
    'externals/styles/mains.css' => 'Improved RTL support',
    'Plugin/Core.php' => 'Improved language support',
    'views/scripts/index/index.tpl' => 'Improved language support',
    'views/scripts/index/language.tpl' => 'Removed',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.2-4.0.3.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    '/application/languages/en/chat.csv' => 'Added missing phrases',
  ),
  '4.0.2' => array(
    'controllers/AdminSettingsController.php' => 'Various level settings fixes and enhancements',
    'Form/Admin/Settings/Level.php' => 'Various level settings fixes and enhancements',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.1-4.0.2.sql' => 'Added',
    'settings/my.sql' => 'Various level settings fixes and enhancements',
  ),
  '4.0.1' => array(
    'controllers/AdminSettingsController.php' => 'Fixed typo',
    'settings/manifest.php' => 'Incremented version',
  ),
) ?>