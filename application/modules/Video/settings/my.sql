
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author		 John
 */

-- --------------------------------------------------------

--
-- Table structure for table `engine4_video_categories`
--

DROP TABLE IF EXISTS `engine4_video_categories`;
CREATE TABLE IF NOT EXISTS `engine4_video_categories` (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) NOT NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `engine4_video_categories`
--

INSERT INTO `engine4_video_categories` (`user_id`, `category_name`) VALUES
(0, 'Autos & Vehicles'),
(0, 'Comedy'),
(0, 'Education'),
(0, 'Entertainment'),
(0, 'Film & Animation'),
(0, 'Gaming'),
(0, 'Howto & Style'),
(0, 'Music'),
(0, 'News & Politics'),
(0, 'Nonprofits & Activism'),
(0, 'People & Blogs'),
(0, 'Pets & Animals'),
(0, 'Science & Technology'),
(0, 'Sports'),
(0, 'Travel & Events');


-- --------------------------------------------------------

--
-- Table structure for table `engine4_video_ratings`
--

DROP TABLE IF EXISTS `engine4_video_ratings`;
CREATE TABLE IF NOT EXISTS `engine4_video_ratings` (
  `video_id` int(10) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `rating` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`video_id`,`user_id`),
  KEY `INDEX` (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_video_videos`
--

DROP TABLE IF EXISTS `engine4_video_videos`;
CREATE TABLE IF NOT EXISTS `engine4_video_videos` (
  `video_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `search` tinyint(1) NOT NULL default '1',
  `owner_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  `type` tinyint(1) NOT NULL,
  `code` varchar(150) NOT NULL,
  `photo_id` int(11) unsigned default NULL,
  `rating` float NOT NULL,
  `category_id` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `duration` int(9) unsigned NOT NULL,
  PRIMARY KEY  (`video_id`),
  KEY `owner_id` (`owner_id`,`owner_type`),
  KEY `search` (`search`),
  KEY `creation_date` (`creation_date`),
  KEY `view_count` (`creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `enabled`, `multi`, `priority`) VALUES
('Video Encode', 'video_encode', 'video', 'Video_Plugin_Job_Encode', 1, 2, 75),
('Rebuild Video Privacy', 'video_maintenance_rebuild_privacy', 'video', 'Video_Plugin_Job_Maintenance_RebuildPrivacy', 1, 1, 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_video_processed', 'video', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_video_processed_failed', 'video', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('video_main', 'standard', 'Video Main Navigation Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_video', 'video', 'Videos', '', '{"route":"video_general"}', 'core_main', '', 7),
('core_sitemap_video', 'video', 'Videos', '', '{"route":"video_general"}', 'core_sitemap', '', 7),
('core_admin_main_plugins_video', 'video', 'Videos', '', '{"route":"admin_default","module":"video","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('video_main_browse', 'video', 'Browse Videos', '', '{"route":"video_general"}', 'video_main', '', 1),
('video_main_manage', 'video', 'My Videos', 'Video_Plugin_Menus', '{"route":"video_general","action":"manage"}', 'video_main', '', 2),
('video_main_create', 'video', 'Post New Video', 'Video_Plugin_Menus', '{"route":"video_general","action":"create"}', 'video_main', '', 3),

('video_admin_main_manage', 'video', 'Manage Videos', '', '{"route":"admin_default","module":"video","controller":"manage"}', 'video_admin_main', '', 1),
('video_admin_main_utility', 'video', 'Video Utilities', '', '{"route":"admin_default","module":"video","controller":"settings","action":"utility"}', 'video_admin_main', '', 2),
('video_admin_main_settings', 'video', 'Global Settings', '', '{"route":"admin_default","module":"video","controller":"settings"}', 'video_admin_main', '', 3),
('video_admin_main_level', 'video', 'Member Level Settings', '', '{"route":"admin_default","module":"video","controller":"settings","action":"level"}', 'video_admin_main', '', 4),
('video_admin_main_categories', 'video', 'Categories', '', '{"route":"admin_default","module":"video","controller":"settings","action":"categories"}', 'video_admin_main', '', 5),

('authorization_admin_level_video', 'video', 'Videos', '', '{"route":"admin_default","module":"video","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('video', 'Videos', 'Videos', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('video.ffmpeg.path', ''),
('video.jobs', 2);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`,  `body`,  `enabled`,  `displayable`,  `attachable`,  `commentable`,  `shareable`, `is_generated`) VALUES
('video_new', 'video', '{item:$subject} posted a new video:', '1', '5', '1', '3', '1', 0),
('comment_video', 'video', '{item:$subject} commented on {item:$owner}''s {item:$object:video}: {body:$body}', 1, 1, 1, 1, 1, 0);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('video_processed', 'video', 'Your {item:$object:video} is ready to be viewed.', 0, ''),
('video_processed_failed', 'video', 'Your {item:$object:video} has failed to process.', 0, '');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment, max
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'max' as `name`,
    3 as `value`,
    '20' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN
-- create, edit, delete, view, comment, upload
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'upload' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, edit, delete, view, comment, upload
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'upload' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'video' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');

