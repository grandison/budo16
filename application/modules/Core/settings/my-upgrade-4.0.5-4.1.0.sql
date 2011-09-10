
/* Create jobs table */
DROP TABLE IF EXISTS `engine4_core_jobs`;
CREATE TABLE IF NOT EXISTS `engine4_core_jobs` (
  `job_id` bigint(20) unsigned NOT NULL auto_increment,
  `jobtype_id` int(10) unsigned NOT NULL,
  `state` enum('pending','active','sleeping','failed','cancelled','completed') NOT NULL default 'pending',
  `is_complete` tinyint(1) unsigned NOT NULL default '0',
  `progress` decimal(5,4) unsigned NOT NULL default '0.0000',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime default NULL,
  `started_date` datetime default NULL,
  `completion_date` datetime default NULL,
  `priority` mediumint(9) NOT NULL default '100',
  `data` text NULL,
  `messages` text NULL,
  PRIMARY KEY  (`job_id`),
  KEY `jobtype_id` (`jobtype_id`),
  KEY `state` (`state`),
  KEY `is_complete` (`is_complete`, `priority`, `job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Create jobtypes table */
DROP TABLE IF EXISTS `engine4_core_jobtypes`;
CREATE TABLE IF NOT EXISTS `engine4_core_jobtypes` (
  `jobtype_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `module` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `form` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '1',
  `priority` mediumint(9) NOT NULL default '100',
  `multi` tinyint(3) unsigned NULL default '1',
  PRIMARY KEY  (`jobtype_id`),
  UNIQUE KEY `plugin` (`plugin`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Create processes table */
DROP TABLE IF EXISTS `engine4_core_processes`;
CREATE TABLE IF NOT EXISTS `engine4_core_processes` (
  `pid` int(10) unsigned NOT NULL,
  `parent_pid` int(10) unsigned NOT NULL default '0',
  `system_pid` int(10) unsigned NOT NULL default '0',
  `started` int(10) unsigned NOT NULL,
  `timeout` mediumint(10) unsigned NOT NULL default '0',
  `name` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY  (`pid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

/* Create tasks table */
DROP TABLE IF EXISTS `engine4_core_tasks`;
CREATE TABLE IF NOT EXISTS `engine4_core_tasks` (
  `task_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `module` varchar(128) NOT NULL default '',
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `timeout` int(11) unsigned NOT NULL default '60',
  `processes` smallint(3) unsigned NOT NULL default '1',
  `semaphore` smallint(3) NOT NULL default '0',
  `started_last` int(11) NOT NULL default '0',
  `started_count` int(11) unsigned NOT NULL default '0',
  `completed_last` int(11) NOT NULL default '0',
  `completed_count` int(11) unsigned NOT NULL default '0',
  `failure_last` int(11) NOT NULL default '0',
  `failure_count` int(11) unsigned NOT NULL default '0',
  `success_last` int(11) NOT NULL default '0',
  `success_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`task_id`),
  UNIQUE KEY `plugin` (`plugin`),
  KEY `module` (`module`),
  KEY `started_last` (`started_last`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

/* Create log table */
DROP TABLE IF EXISTS `engine4_core_log`;
CREATE TABLE IF NOT EXISTS `engine4_core_log` (
  `message_id` bigint(20) unsigned NOT NULL auto_increment,
  `domain` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `plugin` varchar(128) NULL,
  `timestamp` datetime NOT NULL,
  `message` longtext NOT NULL,
  `priority` smallint(2) NOT NULL default '6',
  `priorityName` varchar(16) NOT NULL default 'INFO',
  PRIMARY KEY  (`message_id`),
  KEY `domain` (`domain`, `timestamp`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

/* Adjust tasks and jobs settings */
UPDATE `engine4_core_settings`
SET `value` = '5'
WHERE `name` = 'core.tasks.interval' ;

/* Insert tasks and jobs settings */
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.tasks.count', '1'),
('core.tasks.interval', '60'),
('core.tasks.jobs', '3'),
('core.tasks.key', ''),
('core.tasks.last', '0'),
('core.tasks.mode', 'curl'),
('core.tasks.pid', ''),
('core.tasks.processes', '2'),
('core.tasks.time', '120'),
('core.tasks.timeout', '900')
;

/* Add tasks data */
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Job Queue', 'core', 'Core_Plugin_Task_Jobs', 5),
('Background Mailer', 'core', 'Core_Plugin_Task_Mail', 15),
('Cache Prefetch', 'core', 'Core_Plugin_Task_Prefetch', 300),
('Statistics', 'core', 'Core_Plugin_Task_Statistics', 43200);

/* Add jobtypes data */
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `form`) VALUES
('Download File', 'file_download', 'core', 'Core_Plugin_Job_FileDownload', 'Core_Form_Admin_Job_FileDownload'),
('Upload File', 'file_upload', 'core', 'Core_Plugin_Job_FileUpload', 'Core_Form_Admin_Job_FileUpload');

/* Update menu items table */
UPDATE `engine4_core_menuitems`
SET `order` = 8
WHERE `name` = 'core_admin_main_stats' ;

/* rebuild session table */
DROP TABLE IF EXISTS `engine4_core_session`;
CREATE TABLE `engine4_core_session` (
  `id` char(32) NOT NULL default '',
  `modified` int(11) default NULL,
  `lifetime` int(11) default NULL,
  `data` text,
  `user_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


ALTER TABLE `engine4_core_mail` CHANGE COLUMN `body` `body` longtext NOT NULL ;

ALTER TABLE `engine4_core_pages` ADD COLUMN `levels` text default NULL AFTER `layout` ;
