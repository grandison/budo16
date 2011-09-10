
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_poll_polls`
--

DROP TABLE IF EXISTS `engine4_poll_polls`;
CREATE TABLE IF NOT EXISTS `engine4_poll_polls` (
  `poll_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `is_closed` tinyint(1) NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  `vote_count` int(11) unsigned NOT NULL default '0',
  `search` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`poll_id`),
  KEY `user_id` (`user_id`),
  KEY `is_closed` (`is_closed`),
  KEY `creation_date` (`creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_poll_options`
--

DROP TABLE IF EXISTS `engine4_poll_options`;
CREATE TABLE IF NOT EXISTS `engine4_poll_options` (
  `poll_option_id` int(11) unsigned NOT NULL auto_increment,
  `poll_id` int(11) unsigned NOT NULL,
  `poll_option` text NOT NULL,
  `votes` smallint(4) unsigned NOT NULL,
  PRIMARY KEY  (`poll_option_id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_poll_votes`
--

DROP TABLE IF EXISTS `engine4_poll_votes`;
CREATE TABLE IF NOT EXISTS `engine4_poll_votes` (
  `poll_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `poll_option_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`poll_id`,`user_id`),
  KEY `poll_option_id` (`poll_option_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Poll Privacy', 'poll_maintenance_rebuild_privacy', 'poll', 'Poll_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('poll_main', 'standard', 'Poll Main Navigation Menu'),
('poll_quick', 'standard', 'Poll Quick Navigation Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_poll', 'poll', 'Polls', '', '{"route":"poll_general","action":"browse"}', 'core_main', '', 5),
('core_sitemap_poll', 'poll', 'Polls', '', '{"route":"poll_general","action":"browse"}', 'core_sitemap', '', 5),

('poll_main_browse', 'poll', 'Browse Polls', 'Poll_Plugin_Menus::canViewPolls', '{"route":"poll_general","action":"browse"}', 'poll_main', '', 1),
('poll_main_manage', 'poll', 'My Polls', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"manage"}', 'poll_main', '', 2),
('poll_main_create', 'poll', 'Create New Poll', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"create"}', 'poll_main', '', 3),

('poll_quick_create', 'poll', 'Create New Poll', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"create","class":"buttonlink icon_poll_new"}', 'poll_quick', '', 1),

('core_admin_main_plugins_poll', 'poll', 'Polls', '', '{"route":"admin_default","module":"poll","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('poll_admin_main_manage', 'poll', 'Manage Polls', '', '{"route":"admin_default","module":"poll","controller":"manage"}', 'poll_admin_main', '', 1),
('poll_admin_main_settings', 'poll', 'Global Settings', '', '{"route":"admin_default","module":"poll","controller":"settings"}', 'poll_admin_main', '', 2),
('poll_admin_main_level', 'poll', 'Member Level Settings', '', '{"route":"admin_default","module":"poll","controller":"settings","action":"level"}', 'poll_admin_main', '', 3),

('authorization_admin_level_poll', 'poll', 'Polls', '', '{"route":"admin_default","module":"poll","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('poll', 'Polls', 'Polls', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name` , `value`) VALUES
('polls.maxOptions', '15'),
('polls.showPieChart', '0'),
('polls.canChangeVote', '1');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`,  `body`,  `enabled`,  `displayable`,  `attachable`,  `commentable`,  `shareable`, `is_generated`) VALUES
('poll_new', 'poll', '{item:$subject} created a new poll:', '1', '5', '1', '3', '1', 1),
('comment_poll', 'poll', '{item:$subject} commented on {item:$owner}''s {item:$object:poll}.', 1, 1, 1, 1, 1, 1);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, edit, delete, view, comment
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'vote' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, edit, delete, view, comment
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'vote' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');

