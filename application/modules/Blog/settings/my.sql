/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8441 2011-02-10 23:59:11Z john $
 * @author     Jung
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_blog_blogs`
--

DROP TABLE IF EXISTS `engine4_blog_blogs`;
CREATE TABLE `engine4_blog_blogs` (
  `blog_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `body` longtext NOT NULL,
  `owner_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL default '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  `search` tinyint(1) NOT NULL default '1',
  `draft` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`blog_id`),
  KEY `owner_type` (`owner_type`, `owner_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_blog_categories`
--

DROP TABLE IF EXISTS `engine4_blog_categories`;
CREATE TABLE `engine4_blog_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_blog_categories`
--

INSERT IGNORE INTO `engine4_blog_categories` (`category_id`, `user_id`, `category_name`) VALUES
(1, 1, 'Arts & Culture'),
(2, 1, 'Business'),
(3, 1, 'Entertainment'),
(5, 1, 'Family & Home'),
(6, 1, 'Health'),
(7, 1, 'Recreation'),
(8, 1, 'Personal'),
(9, 1, 'Shopping'),
(10, 1, 'Society'),
(11, 1, 'Sports'),
(12, 1, 'Technology'),
(13, 1, 'Other');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Blog Privacy', 'blog_maintenance_rebuild_privacy', 'blog', 'Blog_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('blog_main', 'standard', 'Blog Main Navigation Menu'),
('blog_quick', 'standard', 'Blog Quick Navigation Menu'),
('blog_gutter', 'standard', 'Blog Gutter Navigation Menu')
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_blog', 'blog', 'Blogs', '', '{"route":"blog_general"}', 'core_main', '', 4),

('core_sitemap_blog', 'blog', 'Blogs', '', '{"route":"blog_general"}', 'core_sitemap', '', 4),

('blog_main_browse', 'blog', 'Browse Entries', 'Blog_Plugin_Menus::canViewBlogs', '{"route":"blog_general"}', 'blog_main', '', 1),
('blog_main_manage', 'blog', 'My Entries', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"manage"}', 'blog_main', '', 2),
('blog_main_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"create"}', 'blog_main', '', 3),

('blog_quick_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"create","class":"buttonlink icon_blog_new"}', 'blog_quick', '', 1),
('blog_quick_style', 'blog', 'Edit Blog Style', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"style","class":"smoothbox buttonlink icon_blog_style"}', 'blog_quick', '', 2),

('blog_gutter_list', 'blog', 'View All Entries', 'Blog_Plugin_Menus', '{"route":"blog_view","class":"buttonlink icon_blog_viewall"}', 'blog_gutter', '', 1),
('blog_gutter_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"create","class":"buttonlink icon_blog_new"}', 'blog_gutter', '', 2),
('blog_gutter_style', 'blog', 'Edit Blog Style', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"style","class":"smoothbox buttonlink icon_blog_style"}', 'blog_gutter', '', 3),
('blog_gutter_edit', 'blog', 'Edit This Entry', 'Blog_Plugin_Menus', '{"route":"blog_specific","action":"edit","class":"buttonlink icon_blog_edit"}', 'blog_gutter', '', 4),
('blog_gutter_delete', 'blog', 'Delete This Entry', 'Blog_Plugin_Menus', '{"route":"blog_specific","action":"delete","class":"buttonlink smoothbox icon_blog_delete"}', 'blog_gutter', '', 5),
('blog_gutter_share', 'blog', 'Share', 'Blog_Plugin_Menus', '{"route":"default","module":"activity","controller":"index","action":"share","class":"buttonlink smoothbox icon_comments"}', 'blog_gutter', '', 6),
('blog_gutter_report', 'blog', 'Report', 'Blog_Plugin_Menus', '{"route":"default","module":"core","controller":"report","action":"create","class":"buttonlink smoothbox icon_report"}', 'blog_gutter', '', 7),

('core_admin_main_plugins_blog', 'blog', 'Blogs', '', '{"route":"admin_default","module":"blog","controller":"manage"}', 'core_admin_main_plugins', '', 999),

('blog_admin_main_manage', 'blog', 'View Blogs', '', '{"route":"admin_default","module":"blog","controller":"manage"}', 'blog_admin_main', '', 1),
('blog_admin_main_settings', 'blog', 'Global Settings', '', '{"route":"admin_default","module":"blog","controller":"settings"}', 'blog_admin_main', '', 2),
('blog_admin_main_level', 'blog', 'Member Level Settings', '', '{"route":"admin_default","module":"blog","controller":"level"}', 'blog_admin_main', '', 3),
('blog_admin_main_categories', 'blog', 'Categories', '', '{"route":"admin_default","module":"blog","controller":"settings", "action":"categories"}', 'blog_admin_main', '', 4),

('authorization_admin_level_blog', 'blog', 'Blogs', '', '{"route":"admin_default","module":"blog","controller":"level","action":"index"}', 'authorization_admin_level', '', 999)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('blog', 'Blogs', 'Blogs', '4.1.2', 1, 'extra');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('blog_new', 'blog', '{item:$subject} wrote a new blog entry:', 1, 5, 1, 3, 1, 1),
('comment_blog', 'blog', '{item:$subject} commented on {item:$owner}''s {item:$object:blog entry}: {body:$body}', 1, 1, 1, 1, 1, 0);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment, auth_html
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'auth_html' as `name`,
    3 as `value`,
    'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, delete, edit, view, comment, css, style, max
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'max' as `name`,
    3 as `value`,
    1000 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, delete, edit, view, comment, css, style, max
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'style' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'max' as `name`,
    3 as `value`,
    50 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'blog' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');

