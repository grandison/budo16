
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Music Privacy', 'music_maintenance_rebuild_privacy', 'music', 'Music_Plugin_Job_Maintenance_RebuildPrivacy', 50);

DELETE FROM `engine4_core_tasks` WHERE `module` = 'music' || `plugin` = 'Music_Plugin_Task_Cleanup' ;

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Music Cleanup', 'music', 'Music_Plugin_Task_Cleanup',	43200);


INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('music_main',  'standard', 'Music Main Navigation Menu'),
('music_quick', 'standard', 'Music Quick Navigation Menu')
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('music_quick_create', 'music', 'Upload Music', 'Music_Plugin_Menus::canCreatePlaylists', '{"route":"music_general","action":"create","class":"buttonlink icon_music_new"}', 'music_quick', '', 100),
('music_main_browse',  'music', 'Browse Music', 'Music_Plugin_Menus::canViewPlaylists',   '{"route":"music_general","action":"browse"}', 'music_main', '', 1),
('music_main_manage',  'music', 'My Music',     'Music_Plugin_Menus::canCreatePlaylists', '{"route":"music_general","action":"manage"}', 'music_main', '', 2),
('music_main_create',  'music', 'Upload Music', 'Music_Plugin_Menus::canCreatePlaylists', '{"route":"music_general","action":"create"}', 'music_main', '', 3)
;


UPDATE `engine4_core_menuitems`
  SET `params` = '{"route":"music_general","action":"browse"}'
  WHERE `name` = 'core_main_music' ;

UPDATE `engine4_core_menuitems`
  SET `params` = '{"route":"music_general","action":"browse"}'
  WHERE `name` = 'core_sitemap_music' ;

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"music","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_music' LIMIT 1;



ALTER TABLE `engine4_music_playlists`
  ADD COLUMN `view_count` int(11) unsigned NOT NULL default '0'
  AFTER `modified_date` ;

ALTER TABLE `engine4_music_playlists`
  ADD COLUMN `comment_count` int(11) unsigned NOT NULL default '0'
  AFTER `view_count` ;

/* add comments for comment count */
UPDATE `engine4_music_playlists`
  SET `comment_count` = (
    SELECT COUNT(*)
    FROM `engine4_core_comments`
    WHERE `resource_type` = 'music_playlist' &&
      `resource_id` = `engine4_music_playlists`.`playlist_id`
);


INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES ('music.playlistsperpage', 10);

UPDATE `engine4_core_settings` SET `name` = 'music.playlistsperpage' WHERE `name` = 'music.playlistsPerPage' ;
