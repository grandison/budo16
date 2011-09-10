<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8878 2011-04-13 19:12:12Z jung $
 * @author     Sami
 */
return array(
  '4.1.4' => array(
    'controllers/NetworkController.php' => 'Members can join hidden networks',
    'externals/styles/main.css' => 'Added svn:keywords Id',
    'externals/styles/mobile.css' => 'Added',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.3' => array(
    '/application/languages/en/network.csv' => 'Added phrases',
    'controllers/AdminManageController.php' => 'Added support to list members in a network',
    'controllers/NetworkController.php' => 'Fixed ordering; Network names are now translated',
    'externals/styles/main.css' => 'Added styles for updated network settings page',
    'Form/Admin/Network.php' => 'Added support for multiple options for one network',
    'Model/Network.php' => 'Added support for multiple options for one network',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/helpers/NetworkField.php' => 'Added support for multiple options for one network',
    'views/scripts/admin-manage/index.tpl' => 'Added smoothbox link to list members in a network',
    'views/scripts/admin-manage/members.tpl' => 'Added support to list members in a network',
  ),
  '4.1.2' => array(
    'settings/changelog.php' => 'Incremented version',
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
    'externals/styles/main.css' => 'Added styles for network join feed item type',
    'Form/Admin/Network.php' => 'Fixed issue with country fields',
    'Plugin/Job/Maintenance/RebuildMembership.php' => 'Added',
    'Plugin/Task/Maintenance/RebuildMembership.php' => 'Removed',
    'Plugin/User.php' => 'Fixed issue with removing users when they delete their accounts',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Backwards compatibility fix for tasks modifications',
    'settings/my-upgrade-4.0.5p1-4.1.0.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/helpers/NetworkField.php' => 'Fixed issue with country fields',
  ),
  '4.0.5p1' => array(
    'Model/Network.php' => 'Fixed issue where networks would get indexed in global search',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.5-4.0.5p1.sql' => 'Added',
  ),
  '4.0.5' => array(
    'externals/images/nophoto_network_thumb_icon.png' => 'Added',
    'Plugin/Task/Maintenance/RebuildMembership.php' => 'Added idle support',
    'settings/changelog.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/delete.tpl' => 'Tweak for parent refresh',
  ),
  '4.0.4' => array(
    'externals/styles/main.css' => 'Improved RTL support',
    'Model/Network.php' => 'Fixes to improve memory leak issue in network admin panel page',
    'Plugin/Task/Maintenance/RebuildMembership.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.0.3' => array(
    'Model/Network.php' => 'Fixed multi checkbox and multi select field support',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    '/application/languages/en/network.csv' => 'Added phrases',
  ),
  '4.0.2' => array(
    'controllers/NetworkController.php' => 'Fixed missing check for invisible networks',
    'settings/manifest.php' => 'Incremented version',
  ),
  '4.0.1' => array(
    'controllers/AdminManageController.php' => 'Added missing pagination',
    'settings/manifest.php' => 'Incremented version',
    'views/scripts/admin-manage/index.tpl' => 'Added missing pagination',
    'network.csv' => 'Repair to invalid language string.',
  ),
) ?>