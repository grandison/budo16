/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my-upgrade-4.0.0beta3-4.0.0rc1.sql 8052 2010-12-14 03:11:20Z john $
 * @author     John
 */

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.facebook.enable','none');

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('album_admin_main_categories', 'album', 'Categories', '', '{"route":"admin_default","module":"album","controller":"settings", "action":"categories"}', 'album_admin_main', '', 4)
;

/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`plugin`, `timeout`, `enabled`, `executed_last`, `executed_count`, `failure_count`, `success_count`) VALUES
('Core_Plugin_Task_Statistics', 43200, 1, 0, 0, 0, 0);
*/

UPDATE `engine4_core_menuitems` SET
  `name` = 'core_admin_main_manage_packages',
  `label` = 'Packages & Plugins',
  `params` = '{"route":"admin_default","module":"core","controller":"packages"}'
WHERE
  `name` = 'core_admin_main_manage_plugins';

ALTER TABLE `engine4_core_modules` CHANGE `version` `version` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;