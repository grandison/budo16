
DROP TABLE IF EXISTS `engine4_core_bannedemails`;
CREATE TABLE IF NOT EXISTS `engine4_core_bannedemails` (
  `bannedemail_id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `whitelist` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`bannedemail_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `engine4_core_bannedips`;
CREATE TABLE IF NOT EXISTS `engine4_core_bannedips` (
  `bannedip_id` int(10) unsigned NOT NULL auto_increment,
  `start` bigint(20) NOT NULL,
  `stop` bigint(20) NOT NULL,
  PRIMARY KEY (`bannedip_id`),
  KEY `start` (`start`, `stop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `engine4_core_bannedwords`;
CREATE TABLE IF NOT EXISTS `engine4_core_bannedwords` (
  `bannedword_id` int(10) unsigned NOT NULL auto_increment,
  `word` text NOT NULL,
  PRIMARY KEY  (`bannedword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_banning_general', 'core', 'Spam & Banning Tools', '', '{"route":"core_admin_settings","action":"spam"}', 'core_admin_banning', '', 1);
