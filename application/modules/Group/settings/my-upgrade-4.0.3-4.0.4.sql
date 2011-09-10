
/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Group Privacy', 'rebuild_privacy', 'group', 'Group_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

REPLACE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_group_accepted', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_approve', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_discussion_reply', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_discussion_response', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_invite', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_promote', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');

UPDATE `engine4_authorization_permissions`
SET `value` = '["registered", "member","officer", "owner"]'
WHERE `value` = '["registered", member","officer", "owner"]' ;

UPDATE `engine4_core_pages` SET custom = 0 WHERE `name` = 'group_profile_index' LIMIT 1;

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('group_main', 'standard', 'Group Main Navigation Menu'),
('group_profile', 'standard', 'Group Profile Options Menu')
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_group', 'group', 'Groups', '', '{"route":"admin_default","module":"group","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;
