
ALTER TABLE `engine4_album_photos` ADD COLUMN
  `view_count` int(11) unsigned NOT NULL default '0';

ALTER TABLE `engine4_album_photos` ADD COLUMN
  `comment_count` int(11) unsigned NOT NULL default '0';

UPDATE `engine4_album_photos` SET `comment_count` =
  (SELECT COUNT(*) FROM `engine4_core_comments` WHERE `resource_type` = 'album_photo' && `resource_id` = `engine4_album_photos`.`photo_id`) ;
