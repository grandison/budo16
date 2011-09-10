
ALTER TABLE `engine4_event_membership` CHANGE COLUMN
  `rsvp` `rsvp` tinyint(3) NOT NULL default '3';

/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Event Privacy', 'rebuild_privacy', 'event', 'Event_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

REPLACE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_event_accepted', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_approve', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_discussion_response', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_discussion_reply', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_invite', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');

UPDATE `engine4_core_pages` SET custom = 0 WHERE `name` = 'event_profile_index' LIMIT 1;

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('event_main', 'standard', 'Event Main Navigation Menu'),
('event_profile', 'standard', 'Event Profile Options Menu')
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_event', 'event', 'Events', '', '{"route":"admin_default","module":"event","controller":"level","action":"index"}', 'authorization_admin_level', '', 999)
;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'auth_photo' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');


UPDATE `engine4_authorization_permissions`
SET `params` = '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]'
WHERE
  `params` = '["everyone","owner_network","owner_member_member","owner_member","owner"]' &&
  `type` = 'event' &&
  `name` IN('auth_view', 'auth_comment', 'auth_photo')
