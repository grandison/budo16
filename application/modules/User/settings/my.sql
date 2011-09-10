
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8942 2011-05-16 21:40:10Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_users`
--

DROP TABLE IF EXISTS `engine4_users`;
CREATE TABLE `engine4_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(128) NOT NULL,
  `username` varchar(128) default NULL,
  `displayname` varchar(128) NOT NULL default '',
  `photo_id` int(11) unsigned NOT NULL default '0',
  `status` text NULL,
  `status_date` datetime NULL,
  `password` char(32) NOT NULL,
  `salt` char(64) NOT NULL,
  `locale` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'auto',
  `language` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'en_US',
  `timezone` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'America/Los_Angeles',
  `search` tinyint(1) NOT NULL default '1',
  `show_profileviewers` tinyint(1) NOT NULL default '1',
  `level_id` int(11) unsigned NOT NULL,
  `invites_used` int(11) unsigned NOT NULL default '0',
  `extra_invites` int(11) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL default '1',   
  `verified` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `creation_date` datetime NOT NULL,
  `creation_ip` bigint(11) NOT NULL,
  `modified_date` datetime NOT NULL,
  `lastlogin_date` datetime default NULL,
  `lastlogin_ip` int(11) default NULL,
  `update_date` int(11) default NULL,
  `member_count` smallint(5) unsigned NOT NULL default '0',
  `view_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `USERNAME` (`username`),
  KEY `MEMBER_COUNT` (`member_count`),
  KEY `CREATION_DATE` (`creation_date`),
  KEY `search` (`search`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_block`
--

DROP TABLE IF EXISTS `engine4_user_block`;
CREATE TABLE IF NOT EXISTS `engine4_user_block` (
  `user_id` int(11) unsigned NOT NULL,
  `blocked_user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`blocked_user_id`),
  KEY `REVERSE` (`blocked_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_facebook`
--

DROP TABLE IF EXISTS `engine4_user_facebook`;
CREATE TABLE IF NOT EXISTS `engine4_user_facebook` (
  `user_id` int(11) unsigned NOT NULL,
  `facebook_uid` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `facebook_uid` (`facebook_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_forgot`
--

DROP TABLE IF EXISTS `engine4_user_forgot`;
CREATE TABLE IF NOT EXISTS `engine4_user_forgot` (
  `user_id` int(11) unsigned NOT NULL,
  `code` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_listitems`
--

DROP TABLE IF EXISTS `engine4_user_listitems`;
CREATE TABLE IF NOT EXISTS `engine4_user_listitems` (
  `listitem_id` int(11) unsigned NOT NULL auto_increment,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_lists`
--

DROP TABLE IF EXISTS `engine4_user_lists`;
CREATE TABLE IF NOT EXISTS `engine4_user_lists` (
  `list_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `owner_id` int(11) unsigned NOT NULL,
  `child_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`list_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_logins`
--

DROP TABLE IF EXISTS `engine4_user_logins`;
CREATE TABLE IF NOT EXISTS `engine4_user_logins` (
  `login_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned default NULL,
  `email` varchar(128) default NULL,
  `ip` bigint(20) NOT NULL,
  `timestamp` datetime NOT NULL,
  `state` enum('success','no-member','bad-password','disabled','unpaid','third-party','v3-migration','unknown') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'unknown',
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`login_id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_membership`
--

DROP TABLE IF EXISTS `engine4_user_membership`;
CREATE TABLE `engine4_user_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `resource_approved` tinyint(1) NOT NULL default '0',
  `user_approved` tinyint(1) NOT NULL default '0',
  `message` text default NULL,
  `description` text default NULL,
  PRIMARY KEY  (`resource_id`, `user_id`),
  KEY `REVERSE` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_online`
--

DROP TABLE IF EXISTS `engine4_user_online`;
CREATE TABLE IF NOT EXISTS `engine4_user_online` (
  `ip` bigint(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL default '0',
  `active` datetime NOT NULL,
  PRIMARY KEY  (`ip`,`user_id`),
  KEY `LOOKUP` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_verify`
--

DROP TABLE IF EXISTS `engine4_user_verify`;
CREATE TABLE IF NOT EXISTS `engine4_user_verify` (
  `user_id` int(11) unsigned NOT NULL,
  `code` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_signup`
--

DROP TABLE IF EXISTS `engine4_user_signup`;
CREATE TABLE IF NOT EXISTS `engine4_user_signup` (
  `signup_id` int(11) unsigned NOT NULL auto_increment,
  `class` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `order` smallint(6) NOT NULL default '999',
  `enable` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`signup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_user_signup`
--

INSERT INTO `engine4_user_signup` (`signup_id`, `class`, `order`, `enable`) VALUES
(1, 'User_Plugin_Signup_Account', 1, 1),
(2, 'User_Plugin_Signup_Fields', 2, 1),
(3, 'User_Plugin_Signup_Photo', 3, 1),
(4, 'User_Plugin_Signup_Invite', 4, 0);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Member Privacy', 'user_maintenance_rebuild_privacy', 'user', 'User_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('user_account_approved', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo]'),
('notify_friend_accepted', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_friend_request', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_friend_follow_request', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_friend_follow_accepted', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_friend_follow', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_post_user', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_tagged', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_user', 'user', 'Members', '', '{"route":"user_general","action":"browse"}', 'core_main', '', 2),

('core_sitemap_user', 'user', 'Members', '', '{"route":"user_general","action":"browse"}', 'core_sitemap', '', 2),

('user_home_updates', 'user', 'View Recent Updates', '', '{"route":"recent_activity","icon":"application/modules/User/externals/images/links/updates.png"}', 'user_home', '', 1),
('user_home_view', 'user', 'View My Profile', 'User_Plugin_Menus', '{"route":"user_profile_self","icon":"application/modules/User/externals/images/links/profile.png"}', 'user_home', '', 2),
('user_home_edit', 'user', 'Edit My Profile', 'User_Plugin_Menus', '{"route":"user_extended","module":"user","controller":"edit","action":"profile","icon":"application/modules/User/externals/images/links/edit.png"}', 'user_home', '', 3),
('user_home_friends', 'user', 'Browse Members', '', '{"route":"user_general","controller":"index","action":"browse","icon":"application/modules/User/externals/images/links/search.png"}', 'user_home', '', 4),

('user_profile_edit', 'user', 'Edit Profile', 'User_Plugin_Menus', '', 'user_profile', '', 1),
('user_profile_friend', 'user', 'Friends', 'User_Plugin_Menus', '', 'user_profile', '', 3),
('user_profile_block', 'user', 'Block', 'User_Plugin_Menus', '', 'user_profile', '', 4),
('user_profile_report', 'user', 'Report User', 'User_Plugin_Menus', '', 'user_profile', '', 5),

('user_edit_profile', 'user', 'Personal Info', '', '{"route":"user_extended","module":"user","controller":"edit","action":"profile"}', 'user_edit', '', 1),
('user_edit_photo', 'user', 'Edit My Photo', '', '{"route":"user_extended","module":"user","controller":"edit","action":"photo"}', 'user_edit', '', 2),
('user_edit_style', 'user', 'Profile Style', '', '{"route":"user_extended","module":"user","controller":"edit","action":"style"}', 'user_edit', '', 3),

('user_settings_general', 'user', 'General', '', '{"route":"user_extended","module":"user","controller":"settings","action":"general"}', 'user_settings', '', 1),
('user_settings_privacy', 'user', 'Privacy', '', '{"route":"user_extended","module":"user","controller":"settings","action":"privacy"}', 'user_settings', '', 2),
('user_settings_notifications', 'user', 'Notifications', '', '{"route":"user_extended","module":"user","controller":"settings","action":"notifications"}', 'user_settings', '', 3),
('user_settings_password', 'user', 'Change Password', '', '{"route":"user_extended", "module":"user", "controller":"settings", "action":"password"}', 'user_settings', '', 5),
('user_settings_delete', 'user', 'Delete Account', 'User_Plugin_Menus::canDelete', '{"route":"user_extended", "module":"user", "controller":"settings", "action":"delete"}', 'user_settings', '', 6),

('core_admin_main_manage_members', 'user', 'Members', '', '{"route":"admin_default","module":"user","controller":"manage"}', 'core_admin_main_manage', '', 1),
('core_admin_main_signup', 'user', 'Signup Process', '', '{"route":"admin_default", "controller":"signup", "module":"user"}', 'core_admin_main_settings', '', 3),
('core_admin_main_facebook', 'user', 'Facebook Integration', '', '{"route":"admin_default", "action":"facebook", "controller":"settings", "module":"user"}', 'core_admin_main_settings', '', 4),
('core_admin_main_settings_friends', 'user', 'Friendship Settings', '', '{"route":"admin_default","module":"user","controller":"settings","action":"friends"}', 'core_admin_main_settings', '', 6),

('user_admin_banning_logins', 'user', 'Login History', '', '{"route":"admin_default","module":"user","controller":"logins","action":"index"}', 'core_admin_banning', '', 2),

('authorization_admin_level_user', 'user', 'Members', '', '{"route":"admin_default","module":"user","controller":"settings","action":"level"}', 'authorization_admin_level', '', 2)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('user_home', 'standard', 'Member Home Quick Links Menu'),
('user_profile', 'standard', 'Member Profile Options Menu'),
('user_edit', 'standard', 'Member Edit Profile Navigation Menu'),
('user_settings', 'standard', 'Member Settings Navigation Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('user', 'Members', 'Members', '4.1.5', 1, 'core');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('user.friends.eligible', '2'),
('user.friends.direction', '1'),
('user.friends.verification', '1'),
('user.friends.lists', '1'),
('user.signup.approve', 1),
('user.signup.checkemail', 1),
('user.signup.inviteonly', 0),
('user.signup.random', 0),
('user.signup.terms', 1),
('user.signup.username', 1),
('user.signup.verifyemail', 0),
('core.facebook.enable', 'none');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_tasks`
--

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Member Data Maintenance', 'user', 'User_Plugin_Task_Cleanup', 60);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('status', 'user', '{item:$subject} {body:$body}', 1, 5, 0, 1, 4, 0),
('post', 'user', '{actors:$subject:$object}: {body:$body}', 1, 7, 1, 1, 1, 0),
('post_self', 'user', '{item:$subject} {body:$body}', 1, 5, 1, 1, 1, 0),

('profile_photo_update', 'user', '{item:$subject} has added a new profile photo.', 1, 5, 1, 1, 1, 1),
('friends', 'user', '{item:$subject} is now friends with {item:$object}.', 1, 3, 0, 1, 1, 1),
('friends_follow', 'user', '{item:$subject} is now following {item:$object}.', 1, 3, 0, 1, 1, 1),
('login', 'user', '{item:$subject} has signed in.', 0, 1, 0, 1, 1, 1),
('logout', 'user', '{item:$subject} has signed out.', 0, 1, 0, 1, 1, 1),
('signup', 'user', '{item:$subject} has just signed up. Say hello!', 1, 5, 0, 1, 1, 1),
('tagged', 'user', '{item:$subject} tagged {item:$object} in a {var:$label}:', 1, 7, 1, 1, 0, 1)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('post_user', 'user', '{item:$subject} has posted on your {item:$object:profile}.', 0, ''),
('friend_accepted', 'user', 'You and {item:$subject} are now friends.', 0, ''),
('friend_request', 'user', '{item:$subject} has requested to be your friend.', 1, 'user.friends.request-friend'),
('friend_follow_request', 'user', '{item:$subject} has requested to follow you.', 1, 'user.friends.request-follow'),
('friend_follow_accepted', 'user', 'You are now following {item:$subject}.', 0, ''),
('friend_follow', 'user', '{item:$subject} is now following you.', 0, ''),
('tagged', 'user', '{item:$subject} tagged you in a {item:$object:$label}.', 0, '')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","registered","network","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","registered","network","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, edit, delete, view, comment, block, search, status, username, style
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'block' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'username' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'style' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'activity' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, edit, delete, view, comment, block, search, status, username, style
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'block' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'username' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_maps`
--

DROP TABLE IF EXISTS `engine4_user_fields_maps`;
CREATE TABLE `engine4_user_fields_maps` (
  `field_id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  `order` smallint(6) NOT NULL,
  PRIMARY KEY  (`field_id`,`option_id`,`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `engine4_user_fields_maps`
--

INSERT IGNORE INTO `engine4_user_fields_maps` (`field_id`, `option_id`, `child_id`, `order`) VALUES
(0, 0, 1, 1),
(1, 1, 2, 2),
(1, 1, 3, 3),
(1, 1, 4, 4),
(1, 1, 5, 5),
(1, 1, 6, 6),
(1, 1, 7, 7),
(1, 1, 8, 8),
(1, 1, 9, 9),
(1, 1, 10, 10),
(1, 1, 11, 11),
(1, 1, 12, 12),
(1, 1, 13, 13)
;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_meta`
--

DROP TABLE IF EXISTS `engine4_user_fields_meta`;
CREATE TABLE `engine4_user_fields_meta` (
  `field_id` int(11) unsigned NOT NULL auto_increment,

  `type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL default '',
  `alias` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default '',
  `required` tinyint(1) NOT NULL default '0',
  `display` tinyint(1) unsigned NOT NULL,
  `publish` tinyint(1) unsigned NOT NULL default '0',
  `search` tinyint(1) unsigned NOT NULL default '0',
  `show` tinyint(1) unsigned NOT NULL default '1',
  `order` smallint(3) unsigned NOT NULL default '999',

  `config` text NULL,
  `validators` text NULL,
  `filters` text NULL,

  `style` text NULL,
  `error` text NULL,

  PRIMARY KEY  (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_fields`
--

INSERT IGNORE INTO `engine4_user_fields_meta` (`field_id`, `type`, `label`, `description`, `alias`, `required`, `config`, `validators`, `filters`, `display`, `search`) VALUES
(1, 'profile_type', 'Profile Type', '', 'profile_type', 1, '', NULL, NULL, 0, 2),
(2, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0),
(3, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2),
(4, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2),
(5, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1),
(6, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1),
(7, 'heading', 'Contact Information', '', '', 0, '', NULL, NULL, 1, 0),
(8, 'website', 'Website', '', '', 0, '', NULL, NULL, 1, 0),
(9, 'twitter', 'Twitter', '', '', 0, '', NULL, NULL, 1, 0),
(10, 'facebook', 'Facebook', '', '', 0, '', NULL, NULL, 1, 0),
(11, 'aim', 'AIM', '', '', 0, '', NULL, NULL, 1, 0),
(12, 'heading', 'Personal Details', '', '', 0, '', NULL, NULL, 1, 0),
(13, 'about_me', 'About Me', '', '', 0, '', NULL, NULL, 1, 0);


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_options`
--

DROP TABLE IF EXISTS `engine4_user_fields_options`;
CREATE TABLE `engine4_user_fields_options` (
  `option_id` int(11) unsigned NOT NULL auto_increment,
  `field_id` int(11) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `order` smallint(6) NOT NULL default '999',
  PRIMARY KEY  (`option_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_options`
--

INSERT IGNORE INTO `engine4_user_fields_options` (`option_id`, `field_id`, `label`, `order`) VALUES
(1, 1, 'Regular Member', 1),
(2, 5, 'Male', 1),
(3, 5, 'Female', 2);


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_values`
--

DROP TABLE IF EXISTS `engine4_user_fields_values`;
CREATE TABLE `engine4_user_fields_values` (
  `item_id` int(11) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  `index` smallint(3) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`item_id`,`field_id`,`index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_values`
--


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_search`
--

DROP TABLE IF EXISTS `engine4_user_fields_search`;
CREATE TABLE IF NOT EXISTS `engine4_user_fields_search` (
  `item_id` int(11) unsigned NOT NULL,
  `profile_type` smallint(11) unsigned NULL,
  `first_name` varchar(255) NULL,
  `last_name` varchar(255) NULL,
  `gender` smallint(6) unsigned NULL,
  `birthdate` date NULL,
  PRIMARY KEY  (`item_id`),
  KEY (`profile_type`),
  KEY (`first_name`),
  KEY (`last_name`),
  KEY (`gender`),
  KEY (`birthdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
