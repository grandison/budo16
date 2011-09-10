
ALTER TABLE `engine4_core_pages`
  ADD COLUMN `provides` text default NULL AFTER `levels` ;

UPDATE `engine4_core_pages`
SET `provides` = 'header-footer'
WHERE `name` = 'header' ;

UPDATE `engine4_core_pages`
SET `provides` = 'header-footer'
WHERE `name` = 'footer' ;

UPDATE `engine4_core_pages`
SET `provides` = 'no-viewer;no-subject'
WHERE `name` = 'core_index_index' ;

UPDATE `engine4_core_pages`
SET `provides` = 'viewer;no-subject'
WHERE `name` = 'user_index_home' ;

UPDATE `engine4_core_pages`
SET `provides` = 'subject=user'
WHERE `name` = 'user_profile_index' ;

UPDATE `engine4_core_pages`
SET `provides` = 'no-subject'
WHERE `custom` = 1 ;

ALTER TABLE `engine4_core_comments`
  ADD COLUMN `like_count` int(11) unsigned NOT NULL default '0' ;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"admin_default","controller":"log","action":"index"}'
WHERE `name` = 'core_admin_main_stats_logs' ;

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.log.adapter', 'file');

/* Fix some incorrect default collations */
ALTER TABLE `engine4_core_mailrecipients` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
ALTER TABLE `engine4_core_mailtemplates` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
ALTER TABLE `engine4_core_themes` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
ALTER TABLE `engine4_core_status` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

ALTER TABLE `engine4_core_mailrecipients`
  CHANGE `email` `email` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NULL ;

DROP TABLE IF EXISTS `engine4_core_nodes`;
CREATE TABLE IF NOT EXISTS `engine4_core_nodes` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `signature` char(40) NOT NULL,
  `host` varchar(255) NOT NULL,
  `ip` bigint(20) NOT NULL,
  `first_seen` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  PRIMARY KEY (`node_id`),
  UNIQUE KEY `signature` (`signature`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
