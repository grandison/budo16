
ALTER TABLE `engine4_album_photos`
  ADD COLUMN `order` int(11) unsigned NOT NULL default '0' ;

UPDATE `engine4_album_photos`
SET `order` = `photo_id` ;
