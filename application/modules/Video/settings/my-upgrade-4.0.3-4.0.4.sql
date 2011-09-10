
/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET `module` = 'video'
WHERE `plugin` = 'Video_Plugin_Task_Encode';
*/

/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Video Privacy', 'rebuild_privacy', 'video', 'Video_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

REPLACE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_video_processed', 'video', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');

DELETE FROM `engine4_activity_notificationtypes` WHERE `module` = 'video' ;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('video_processed', 'video', 'Your {item:$object:video} is ready to be viewed.', 0, '');

DELETE FROM `engine4_activity_notifications` WHERE `type` IN(
  'commented_video',
  'comment_video',
  'liked_video',
  'like_video'
);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_video', 'video', 'Videos', '', '{"route":"admin_default","module":"video","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'max' as `name`,
    3 as `value`,
    '20' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');