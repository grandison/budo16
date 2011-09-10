
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_bans`
--

DROP TABLE IF EXISTS `engine4_chat_bans`;
CREATE TABLE IF NOT EXISTS `engine4_chat_bans` (
  `ban_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) default NULL,
  `description` varchar(255) NOT NULL default '',
  `expires` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ban_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_events`
--

DROP TABLE IF EXISTS `engine4_chat_events`;
CREATE TABLE IF NOT EXISTS `engine4_chat_events` (
  `event_id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `type` varchar(64) collate utf8_unicode_ci NOT NULL,
  `body` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`event_id`),
  KEY `user_id` (`user_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_messages`
--

DROP TABLE IF EXISTS `engine4_chat_messages`;
CREATE TABLE IF NOT EXISTS `engine4_chat_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `system` tinyint(1) NOT NULL default 0,
  `body` text collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (`message_id`),
  KEY `room_id` (`room_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_rooms`
--

DROP TABLE IF EXISTS `engine4_chat_rooms`;
CREATE TABLE IF NOT EXISTS `engine4_chat_rooms` (
  `room_id` int(11) NOT NULL auto_increment,
  `title` varchar(64) collate utf8_unicode_ci default NULL,
  `user_count` smallint(6) NOT NULL,
  `modified_date` datetime NOT NULL,
  `public` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`room_id`),
  KEY `public` (`public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `engine4_chat_rooms`
--

INSERT IGNORE INTO `engine4_chat_rooms` (`room_id`, `title`, `user_count`, `modified_date`) VALUES
(1, 'General Chat', 0, '2010-02-02 00:44:04'),
(2, 'Introduce Yourself', 0, '2010-02-02 00:44:04');


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_roomusers`
--

DROP TABLE IF EXISTS `engine4_chat_roomusers`;
CREATE TABLE IF NOT EXISTS `engine4_chat_roomusers` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL default '1',
  `date` datetime NOT NULL,
  PRIMARY KEY  (`room_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`)
) ENGINE=Memory DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_users`
--

DROP TABLE IF EXISTS `engine4_chat_users`;
CREATE TABLE IF NOT EXISTS `engine4_chat_users` (
  `user_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL default '1',
  `date` datetime NOT NULL,
  `event_count` smallint NOT NULL default 0,
  PRIMARY KEY  (`user_id`),
  KEY `date` (`date`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_chat_whispers`
--

DROP TABLE IF EXISTS `engine4_chat_whispers`;
CREATE TABLE IF NOT EXISTS `engine4_chat_whispers` (
  `whisper_id` bigint(20) NOT NULL auto_increment,
  `recipient_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `recipient_deleted` tinyint(1) NOT NULL default '0',
  `sender_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`whisper_id`),
  KEY `recipient_id` (`recipient_id`),
  KEY `sender_id` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_chat', 'chat', 'Chat', '', '{"route":"default","module":"chat"}', 'core_main', '', 5),
('core_sitemap_chat', 'chat', 'Chat', '', '{"route":"default","module":"chat"}', 'core_sitemap', '', 5),

('core_admin_main_plugins_chat', 'chat', 'Chat', '', '{"route":"admin_default","module":"chat","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('chat_admin_main_manage', 'chat', 'Manage Chat Rooms', '', '{"route":"admin_default","module":"chat","controller":"manage"}', 'chat_admin_main', '', 1),
('chat_admin_main_settings', 'chat', 'Global Settings', '', '{"route":"admin_default","module":"chat","controller":"settings"}', 'chat_admin_main', '', 2),
('chat_admin_main_level', 'chat', 'Member Level Settings', '', '{"route":"admin_default","module":"chat","controller":"settings","action":"level"}', 'chat_admin_main', '', 3),

('authorization_admin_level_chat', 'chat', 'Chat', '', '{"route":"admin_default","module":"chat","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('chat', 'Chat', 'Chat', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('chat.general.delay', '5000'),
('chat.im.privacy', 'friends');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- chat, im
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'chat' as `type`,
    'chat' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'chat' as `type`,
    'im' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
