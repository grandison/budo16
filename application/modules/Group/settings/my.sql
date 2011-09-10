
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author		 John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_albums`
--

DROP TABLE IF EXISTS `engine4_group_albums` ;
CREATE TABLE `engine4_group_albums` (
  `album_id` int(11) unsigned NOT NULL auto_increment,
  `group_id` int(11) unsigned NOT NULL,

  `title` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `search` tinyint(1) NOT NULL default '1',
  `photo_id` int(11) unsigned NOT NULL default '0',
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  `collectible_count` int(11) unsigned NOT NULL default '0',
   PRIMARY KEY (`album_id`),
   KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_categories`
--

DROP TABLE IF EXISTS `engine4_group_categories` ;
CREATE TABLE IF NOT EXISTS `engine4_group_categories` (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_group_categories`
--

INSERT IGNORE INTO `engine4_group_categories` (`title`) VALUES
('Animals'),
('Business & Finance'),
('Computers & Internet'),
('Cultures & Community'),
('Dating & Relationships'),
('Entertainment & Arts'),
('Family & Home'),
('Games'),
('Government & Politics'),
('Health & Wellness'),
('Hobbies & Crafts'),
('Music'),
('Recreation & Sports'),
('Regional'),
('Religion & Beliefs'),
('Schools & Education'),
('Science')
;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_listitems`
--

DROP TABLE IF EXISTS `engine4_group_listitems`;
CREATE TABLE IF NOT EXISTS `engine4_group_listitems` (
  `listitem_id` int(11) unsigned NOT NULL auto_increment,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_lists`
--

DROP TABLE IF EXISTS `engine4_group_lists`;
CREATE TABLE IF NOT EXISTS `engine4_group_lists` (
  `list_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `owner_id` int(11) unsigned NOT NULL,
  `child_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`list_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_groups`
--

DROP TABLE IF EXISTS `engine4_group_groups`;
CREATE TABLE IF NOT EXISTS `engine4_group_groups` (
  `group_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  
  `title` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) unsigned NOT NULL default '0',
  `search` tinyint(1) NOT NULL default '1',
  `invite` tinyint(1) NOT NULL default '1',
  `approval` tinyint(1) NOT NULL default '0',
  `photo_id` int(11) unsigned NOT NULL default '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `member_count` smallint(6) unsigned NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`),
  KEY `user_id` (`user_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_membership`
--

DROP TABLE IF EXISTS `engine4_group_membership`;
CREATE TABLE IF NOT EXISTS `engine4_group_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `resource_approved` tinyint(1) NOT NULL default '0',
  `user_approved` tinyint(1) NOT NULL default '0',
  `message` text NULL,
  `title` text NULL,
  PRIMARY KEY  (`resource_id`, `user_id`),
  KEY `REVERSE` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_photos`
--

DROP TABLE IF EXISTS `engine4_group_photos`;
CREATE TABLE `engine4_group_photos` (
  `photo_id` int(11) unsigned NOT NULL auto_increment,
  `album_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,

  `title` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `collection_id` int(11) unsigned NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_posts`
--

DROP TABLE IF EXISTS `engine4_group_posts`;
CREATE TABLE IF NOT EXISTS `engine4_group_posts` (
  `post_id` int(11) unsigned NOT NULL auto_increment,
  `topic_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  
  `body` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_topics`
--

DROP TABLE IF EXISTS `engine4_group_topics`;
CREATE TABLE IF NOT EXISTS `engine4_group_topics` (
  `topic_id` int(11) unsigned NOT NULL auto_increment,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  
  `title` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `sticky` tinyint(1) NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `post_count` int(11) unsigned NOT NULL default '0',
  `view_count` int(11) unsigned NOT NULL default '0',
  `lastpost_id` int(11) unsigned NOT NULL default '0',
  `lastposter_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_group_topicwatches`
--

DROP TABLE IF EXISTS `engine4_group_topicwatches`;
CREATE TABLE IF NOT EXISTS `engine4_group_topicwatches` (
  `resource_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `watch` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`resource_id`,`topic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Group Privacy', 'group_maintenance_rebuild_privacy', 'group', 'Group_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_group_accepted', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_approve', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_discussion_reply', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_discussion_response', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_invite', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_group_promote', 'group', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('group_main', 'standard', 'Group Main Navigation Menu'),
('group_profile', 'standard', 'Group Profile Options Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_group', 'group', 'Groups', '', '{"route":"group_general"}', 'core_main', '', 6),

('core_sitemap_group', 'group', 'Groups', '', '{"route":"group_general"}', 'core_sitemap', '', 6),

('group_main_browse', 'group', 'Browse Groups', '', '{"route":"group_general","action":"browse"}', 'group_main', '', 1),
('group_main_manage', 'group', 'My Groups', 'Group_Plugin_Menus', '{"route":"group_general","action":"manage"}', 'group_main', '', 2),
('group_main_create', 'group', 'Create New Group', 'Group_Plugin_Menus', '{"route":"group_general","action":"create"}', 'group_main', '', 3),

('group_quick_create', 'group', 'Create New Group', 'Group_Plugin_Menus::canCreateGroups', '{"route":"group_general","action":"create","class":"buttonlink icon_group_new"}', 'group_quick', '', 1),

('group_profile_edit', 'group', 'Edit Profile', 'Group_Plugin_Menus', '', 'group_profile', '', 1),
('group_profile_style', 'group', 'Edit Styles', 'Group_Plugin_Menus', '', 'group_profile', '', 2),

('group_profile_member', 'group', 'Member', 'Group_Plugin_Menus', '', 'group_profile', '', 3),
('group_profile_report', 'group', 'Report Group', 'Group_Plugin_Menus', '', 'group_profile', '', 4),
('group_profile_share', 'group', 'Share', 'Group_Plugin_Menus', '', 'group_profile', '', 5),
('group_profile_invite', 'group', 'Invite', 'Group_Plugin_Menus', '', 'group_profile', '', 6),
('group_profile_message', 'group', 'Message Members', 'Group_Plugin_Menus', '', 'group_profile', '', 7),

('core_admin_main_plugins_group', 'group', 'Groups', '', '{"route":"admin_default","module":"group","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('group_admin_main_manage', 'group', 'Manage Groups', '', '{"route":"admin_default","module":"group","controller":"manage"}', 'group_admin_main', '', 1),
('group_admin_main_level', 'group', 'Member Level Settings', '', '{"route":"admin_default","module":"group","controller":"settings","action":"level"}', 'group_admin_main', '', 2),
('group_admin_main_categories', 'group', 'Categories', '', '{"route":"admin_default","module":"group","controller":"settings","action":"categories"}', 'group_admin_main', '', 3),

('authorization_admin_level_group', 'group', 'Groups', '', '{"route":"admin_default","module":"group","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('group', 'Groups', 'Groups', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('group_create', 'group', '{item:$subject} created a new group:', 1, 5, 1, 1, 1, 1),
('group_join', 'group', '{item:$subject} joined the group {item:$object}', 1, 3, 1, 1, 1, 1),
('group_promote', 'group', '{item:$subject} has been made an officer for the group {item:$object}', 1, 3, 1, 1, 1, 1),
('group_topic_create', 'group', '{item:$subject} posted a {item:$object:topic} in the group {itemParent:$object:group}: {body:$body}', 1, 3, 1, 1, 1, 1),
('group_topic_reply', 'group', '{item:$subject} replied to a {item:$object:topic} in the group {itemParent:$object:group}: {body:$body}', 1, 3, 1, 1, 1, 1),
('group_photo_upload', 'group', '{item:$subject} added {var:$count} photo(s).', 1, 3, 2, 1, 1, 1)
;

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('group_discussion_response', 'group', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::group topic} you created.', 0, ''),
('group_discussion_reply', 'group', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::group topic} you posted on.', 0, ''),
('group_invite', 'group', '{item:$subject} has invited you to the group {item:$object}.', 1, 'group.widget.request-group'),
('group_approve', 'group', '{item:$subject} has requested to join the group {item:$object}.', 0, ''),
('group_accepted', 'group', 'Your request to join the group {item:$subject} has been approved.', 0, ''),
('group_promote', 'group', 'You were promoted to officer in the group {item:$object}.', 0, '')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment, auth_photo
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone", "registered", "member"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["registered", "member", "officer"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'auth_photo' as `name`,
    5 as `value`,
    '["registered", "member", "officer"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'auth_event' as `name`,
    5 as `value`,
    '["registered", "member", "officer"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, delete, edit, view, comment, photo, style, invite
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo.edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'topic.edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'event' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'invite' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, delete, edit, invite, view, comment, photo, style, invite, photo.edit, topic.edit
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'event' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'invite' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo.edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'topic.edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');
