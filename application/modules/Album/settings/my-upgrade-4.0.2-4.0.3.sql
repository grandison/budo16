
UPDATE `engine4_core_menuitems`
SET `plugin` = 'Album_Plugin_Menus::canCreateAlbums'
WHERE `name` = 'album_main_manage';

UPDATE `engine4_core_menuitems`
SET `plugin` = 'Album_Plugin_Menus::canCreateAlbums'
WHERE `name` = 'album_main_upload';

UPDATE `engine4_core_menuitems`
SET `plugin` = 'Album_Plugin_Menus::canViewAlbums'
WHERE `name` = 'album_main_browse';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('album_quick_upload', 'album', 'Add New Photos', 'Album_Plugin_Menus::canCreateAlbums', '{"route":"album_general","action":"upload","class":"buttonlink icon_photos_new"}', 'album_quick', '', 1)
;

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('album_main', 'standard', 'Album Main Navigation Menu'),
('album_quick', 'standard', 'Album Quick Navigation Menu')
;

