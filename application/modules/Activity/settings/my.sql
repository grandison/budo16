
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8942 2011-05-16 21:40:10Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actions`
--

DROP TABLE IF EXISTS `engine4_activity_actions`;
CREATE TABLE `engine4_activity_actions` (
  `action_id` int(11) unsigned NOT NULL auto_increment,
  `type` varchar(32) NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `body` text NULL,
  `params` text NULL,
  `date` datetime NOT NULL,
  `attachment_count` smallint(3) unsigned NOT NULL default '0',
  `comment_count` mediumint(5) unsigned NOT NULL default '0',
  `like_count` mediumint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`),
  KEY `OBJECT` (`object_type`,`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actionsettings`
--

DROP TABLE IF EXISTS `engine4_activity_actionsettings`;
CREATE TABLE IF NOT EXISTS `engine4_activity_actionsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `publish` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actiontypes`
--

DROP TABLE IF EXISTS `engine4_activity_actiontypes`;
CREATE TABLE IF NOT EXISTS `engine4_activity_actiontypes` (
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `module` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `body` text NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  `displayable` tinyint(1) NOT NULL default '3',
  `attachable` tinyint(1) NOT NULL default '1',
  `commentable` tinyint(1) NOT NULL default '1',
  `shareable` tinyint(1) NOT NULL default '1',
  `is_generated` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_attachments`
--

DROP TABLE IF EXISTS `engine4_activity_attachments`;
CREATE TABLE IF NOT EXISTS `engine4_activity_attachments` (
  `attachment_id` int(11) unsigned NOT NULL auto_increment,
  `action_id` int(11) unsigned NOT NULL,
  `type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `mode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`attachment_id`),
  KEY `action_id` (`action_id`),
  KEY `type_id` (`type`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_comments`
--

DROP TABLE IF EXISTS `engine4_activity_comments`;
CREATE TABLE IF NOT EXISTS `engine4_activity_comments` (
  `comment_id` int(11) unsigned NOT NULL auto_increment,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `body` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `like_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`comment_id`),
  KEY `resource_type` (`resource_id`),
  KEY `poster_type` (`poster_type`, `poster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_likes`
--

DROP TABLE IF EXISTS `engine4_activity_likes`;
CREATE TABLE `engine4_activity_likes` (
  `like_id` int(11) unsigned NOT NULL auto_increment,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`like_id`),
  KEY `resource_id` (`resource_id`),
  KEY `poster_type` (`poster_type`, `poster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notifications`
--

DROP TABLE IF EXISTS `engine4_activity_notifications`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notifications` (
  `notification_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `params` text NULL,
  `read` tinyint(1) NOT NULL default '0',
  `mitigated` tinyint(1) NOT NULL default '0',
  `date` datetime NOT NULL,
  PRIMARY KEY  (`notification_id`),
  KEY `LOOKUP` (`user_id`,`date`),
  KEY `subject` (`subject_type`, `subject_id`),
  KEY `object` (`object_type`, `object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notificationsettings`
--

DROP TABLE IF EXISTS `engine4_activity_notificationsettings`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notificationsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notificationtypes`
--

DROP TABLE IF EXISTS `engine4_activity_notificationtypes`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notificationtypes` (
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `module` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `is_request` tinyint(1) NOT NULL DEFAULT '0',
  `handler` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `default` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('liked', 'activity', '{item:$subject} likes your {item:$object:$label}.', 0, ''),
('commented', 'activity', '{item:$subject} has commented on your {item:$object:$label}.', 0, ''),
('commented_commented', 'activity', '{item:$subject} has commented on a {item:$object:$label} you commented on.', 0, ''),
('liked_commented', 'activity', '{item:$subject} has commented on a {item:$object:$label} you liked.', 0, '')
;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_stream`
--

DROP TABLE IF EXISTS `engine4_activity_stream`;
CREATE TABLE `engine4_activity_stream` (
  `target_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `target_id` int(11) unsigned NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `action_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`target_type`,`target_id`,`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`,`action_id`),
  KEY `OBJECT` (`object_type`,`object_id`,`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Activity Privacy', 'activity_maintenance_rebuild_privacy', 'activity', 'Activity_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_commented_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_liked', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_liked_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_activity', 'activity', 'Activity Feed Settings', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"index"}', 'core_admin_main_settings', '', 4)
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_notifications', 'activity', 'Default Email Notifications', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"notifications"}', 'core_admin_main_settings', '', 11)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('activity', 'Activity', 'Activity', '4.1.5', 1, 'core');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('activity.disallowed', 'N'),
('activity.content', 'everyone'),
('activity.filter', 1),
('activity.length', 15),
('activity.publish', 1),
('activity.userdelete', 1),
('activity.userlength', 5),
('activity.liveupdate', 120000)
;

