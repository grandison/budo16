
ALTER TABLE `engine4_music_playlists`
  ADD COLUMN `special` enum('wall', 'message') default NULL AFTER `profile` ;

UPDATE `engine4_music_playlists`
SET `special` = 'wall'
WHERE `composer` = 1 ;

ALTER TABLE `engine4_music_playlists`
  DROP COLUMN `composer` ;
