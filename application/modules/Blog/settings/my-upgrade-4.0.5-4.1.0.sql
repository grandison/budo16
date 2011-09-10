
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Activity Privacy', 'activity_maintenance_rebuild_privacy', 'activity', 'Activity_Plugin_Job_Maintenance_RebuildPrivacy', 50);

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"blog_specific","action":"delete","class":"buttonlink smoothbox icon_blog_delete"}' WHERE `name` = 'blog_gutter_delete' LIMIT 1;

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"blog","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_blog' LIMIT 1;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('blog_gutter_share', 'blog', 'Share', 'Blog_Plugin_Menus', '{"route":"default","module":"activity","controller":"index","action":"share","class":"buttonlink smoothbox icon_comments"}', 'blog_gutter', '', 5),
('blog_gutter_report', 'blog', 'Report', 'Blog_Plugin_Menus', '{"route":"default","module":"core","controller":"report","action":"create","class":"buttonlink smoothbox icon_report"}', 'blog_gutter', '', 6),
('blog_gutter_style', 'blog', 'Edit Blog Style', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"style","class":"smoothbox buttonlink icon_blog_style"}', 'blog_gutter', '', 7);
