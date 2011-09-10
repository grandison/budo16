/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author     Sami
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_albums`
--

DROP TABLE IF EXISTS `engine4_event_albums` ;
CREATE TABLE `engine4_event_albums` (
  `album_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) unsigned NOT NULL,
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
   KEY (`event_id`),
   KEY (`search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_categories`
--

DROP TABLE IF EXISTS `engine4_event_categories` ;
CREATE TABLE IF NOT EXISTS `engine4_event_categories` (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_event_categories`
--

INSERT IGNORE INTO `engine4_event_categories` (`title`) VALUES
('Arts'),
('Business'),
('Conferences'),
('Festivals'),
('Food'),
('Fundraisers'),
('Galleries'),
('Health'),
('Just For Fun'),
('Kids'),
('Learning'),
('Literary'),
('Movies'),
('Museums'),
('Neighborhood'),
('Networking'),
('Nightlife'),
('On Campus'),
('Organizations'),
('Outdoors'),
('Pets'),
('Politics'),
('Sales'),
('Science'),
('Spirituality'),
('Sports'),
('Technology'),
('Theatre'),
('Other');


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_events`
--

DROP TABLE IF EXISTS `engine4_event_events` ;
CREATE TABLE `engine4_event_events` (
  `event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `parent_type` varchar(64) NOT NULL,
  `parent_id` int(11) unsigned NOT NULL,
  `search` tinyint(1) NOT NULL default '1',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `host` varchar(115) NOT NULL,
  `location` varchar(115) NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  `member_count` int(11) unsigned NOT NULL default '0',
  `approval` tinyint(1) NOT NULL default '0',
  `invite` tinyint(1) NOT NULL default '0',
  `photo_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_type` (`parent_type`, `parent_id`),
  KEY `starttime` (`starttime`),
  KEY `search` (`search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_membership`
--

DROP TABLE IF EXISTS `engine4_event_membership`;
CREATE TABLE IF NOT EXISTS `engine4_event_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `resource_approved` tinyint(1) NOT NULL default '0',
  `user_approved` tinyint(1) NOT NULL default '0',
  `message` text NULL,
  `rsvp` tinyint(3) NOT NULL default '3',
  `title` text NULL,
  PRIMARY KEY  (`resource_id`, `user_id`),
  KEY `REVERSE` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_photos`
--

DROP TABLE IF EXISTS `engine4_event_photos`;
CREATE TABLE `engine4_event_photos` (
  `photo_id` int(11) unsigned NOT NULL auto_increment,
  `album_id` int(11) unsigned NOT NULL,
  `event_id` int(11) unsigned NOT NULL,
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
  KEY (`album_id`),
  KEY (`event_id`),
  KEY (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_posts`
--

DROP TABLE IF EXISTS `engine4_event_posts`;
CREATE TABLE IF NOT EXISTS `engine4_event_posts` (
  `post_id` int(11) unsigned NOT NULL auto_increment,
  `topic_id` int(11) unsigned NOT NULL,
  `event_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `body` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_topics`
--

DROP TABLE IF EXISTS `engine4_event_topics`;
CREATE TABLE IF NOT EXISTS `engine4_event_topics` (
  `topic_id` int(11) unsigned NOT NULL auto_increment,
  `event_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `sticky` tinyint(1) NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `view_count` int(11) unsigned NOT NULL default '0',
  `post_count` int(11) unsigned NOT NULL default '0',
  `lastpost_id` int(11) unsigned NOT NULL,
  `lastposter_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`topic_id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_event_topicwatches`
--

DROP TABLE IF EXISTS `engine4_event_topicwatches`;
CREATE TABLE IF NOT EXISTS `engine4_event_topicwatches` (
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
('Rebuild Event Privacy', 'event_maintenance_rebuild_privacy', 'event', 'Event_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_event_accepted', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_approve', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_discussion_response', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_discussion_reply', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_event_invite', 'event', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('event_main', 'standard', 'Event Main Navigation Menu'),
('event_profile', 'standard', 'Event Profile Options Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_event', 'event', 'Events', '', '{"route":"event_general"}', 'core_main', '', 6),

('core_sitemap_event', 'event', 'Events', '', '{"route":"event_general"}', 'core_sitemap', '', 6),

('event_main_upcoming', 'event', 'Upcoming Events', '', '{"route":"event_upcoming"}', 'event_main', '', 1),
('event_main_past', 'event', 'Past Events', '', '{"route":"event_past"}', 'event_main', '', 2),
('event_main_manage', 'event', 'My Events', 'Event_Plugin_Menus', '{"route":"event_general","action":"manage"}', 'event_main', '', 3),
('event_main_create', 'event', 'Create New Event', 'Event_Plugin_Menus', '{"route":"event_general","action":"create"}', 'event_main', '', 4),

('event_quick_create', 'event', 'Create New Event', 'Event_Plugin_Menus::canCreateEvents', '{"route":"event_general","action":"create","class":"buttonlink icon_event_new"}', 'event_quick', '', 1),

('event_profile_edit', 'event', 'Edit Profile', 'Event_Plugin_Menus', '', 'event_profile', '', 1),
('event_profile_style', 'event', 'Edit Styles', 'Event_Plugin_Menus', '', 'event_profile', '', 2),

('event_profile_member', 'event', 'Member', 'Event_Plugin_Menus', '', 'event_profile', '', 3),
('event_profile_report', 'event', 'Report Event', 'Event_Plugin_Menus', '', 'event_profile', '', 4),
('event_profile_share', 'event', 'Share', 'Event_Plugin_Menus', '', 'event_profile', '', 5),
('event_profile_invite', 'event', 'Invite', 'Event_Plugin_Menus', '', 'event_profile', '', 6),
('event_profile_message', 'event', 'Message Members', 'Event_Plugin_Menus', '', 'event_profile', '', 7),

('core_admin_main_plugins_event', 'event', 'Events', '', '{"route":"admin_default","module":"event","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('event_admin_main_manage', 'event', 'Manage Events', '', '{"route":"admin_default","module":"event","controller":"manage"}', 'event_admin_main', '', 1),
('event_admin_main_level', 'event', 'Member Level Settings', '', '{"route":"admin_default","module":"event","controller":"settings","action":"level"}', 'event_admin_main', '', 2),
('event_admin_main_categories', 'event', 'Categories', '', '{"route":"admin_default","module":"event","controller":"settings","action":"categories"}', 'event_admin_main', '', 3),

('authorization_admin_level_event', 'event', 'Events', '', '{"route":"admin_default","module":"event","controller":"level","action":"index"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('event', 'Events', 'Events', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('event_create', 'event', '{item:$subject} created a new event:', 1, 5, 1, 1, 1, 1),
('event_join', 'event', '{item:$subject} joined the event {item:$object}', 1, 3, 1, 1, 1, 1),
('event_topic_create', 'event', '{item:$subject} posted a {item:$object:topic} in the event {itemParent:$object:event}: {body:$body}', 1, 3, 1, 1, 1, 1),
('event_topic_reply', 'event', '{item:$subject} replied to a {item:$object:topic} in the event {itemParent:$object:event}: {body:$body}', 1, 3, 1, 1, 1, 1),
('event_photo_upload', 'event', '{item:$subject} added {var:$count} photo(s).', 1, 3, 2, 1, 1, 1)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('event_accepted', 'event', 'Your request to join the event {item:$subject} has been approved.', 0, ''),
('event_approve', 'event', '{item:$subject} has requested to join the event {item:$object}.', 0, ''),
('event_discussion_response', 'event', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::event topic} you created.', 0, ''),
('event_discussion_reply', 'event', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::event topic} you posted on.', 0, ''),
('event_invite', 'event', '{item:$subject} has invited you to the event {item:$object}.', 1, 'event.widget.request-event')
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
    'event' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'auth_photo' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, delete, edit, view, comment, photo, style, invite
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'photo' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'invite' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, delete, edit, view, comment, photo, style, invite
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'photo' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'invite' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'event' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');
