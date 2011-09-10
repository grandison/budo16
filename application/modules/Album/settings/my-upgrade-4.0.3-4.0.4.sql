
/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Album Privacy', 'rebuild_privacy', 'album', 'Album_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

CREATE TABLE IF NOT EXISTS `engine4_album_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_album', 'album', 'Photo Albums', '', '{"route":"admin_default","module":"album","controller":"level","action":"index"}', 'authorization_admin_level', '', 999)
;