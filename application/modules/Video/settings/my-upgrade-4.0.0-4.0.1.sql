
ALTER TABLE `engine4_video_videos` ADD COLUMN
  `comment_count` int(11) unsigned NOT NULL default '0' AFTER `view_count`;

UPDATE `engine4_video_videos` SET `comment_count` =
  (SELECT COUNT(*) FROM `engine4_core_comments` WHERE `resource_type` = 'video' && `resource_id` = `engine4_video_videos`.`video_id`) ;
