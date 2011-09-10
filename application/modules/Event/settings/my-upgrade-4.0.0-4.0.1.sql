
ALTER TABLE `engine4_event_photos` ADD COLUMN
  `view_count` int(11) unsigned NOT NULL default '0';

ALTER TABLE `engine4_event_photos` ADD COLUMN
  `comment_count` int(11) unsigned NOT NULL default '0';

UPDATE `engine4_event_photos` SET `comment_count` =
  (SELECT COUNT(*) FROM `engine4_core_comments` WHERE `resource_type` = 'event_photo' && `resource_id` = `engine4_event_photos`.`photo_id`) ;

ALTER TABLE `engine4_event_topics` ADD COLUMN
  `view_count` int(11) unsigned NOT NULL default '0' AFTER `closed`;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'style' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
