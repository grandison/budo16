
/* Search */
DELETE FROM `engine4_core_search` WHERE `type` = 'core_ad';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_adcampaign';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_geotag';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_listitem';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_list_item';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_list';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_page';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_report';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_style';
DELETE FROM `engine4_core_search` WHERE `type` = 'core_tagmap';


/* Tasks */

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `type` enum('disabled','manual','automatic','semi-automatic') NOT NULL default 'automatic'
AFTER `timeout`;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `state` enum('dormant','active','sleeping','ready') NOT NULL default 'dormant'
AFTER `type`;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `data` text NULL
AFTER `state`;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `category` varchar(128) NOT NULL default 'general'
AFTER `data`;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `module` varchar(128) NOT NULL default ''
AFTER `category`;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `state` = 'active' WHERE `executing` = 1;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `category` = 'system', `module` = 'core' WHERE `plugin` IN(
  'Core_Plugin_Task_Mail',
  'Core_Plugin_Task_Statistics'
);
*/


/* Mail Templates */
ALTER TABLE `engine4_core_mailtemplates`
ADD COLUMN `module` varchar(64) NOT NULL default ''
AFTER `type`;

DELETE FROM `engine4_core_mailtemplates` WHERE `type` IN('core_invite', 'core_invitecode') ;

REPLACE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('header', 'core', ''),
('footer', 'core', ''),
('header_member', 'core', ''),
('footer_member', 'core', ''),
('core_contact', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_name],[sender_email],[sender_link],[sender_photo],[message]'),
('core_verification', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link]'),
('core_verification_password', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[password]'),
('core_welcome', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link]'),
('core_welcome_password', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[password]'),
('core_lostpassword', 'core', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link]');


/* Menus */
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_tasks', 'core', 'Task Scheduler', '', '{"route":"admin_default","controller":"tasks"}', 'core_admin_main_settings', '', 10)
;

DELETE FROM `engine4_core_menuitems` WHERE `name` = 'core_admin_levels_general' ;
