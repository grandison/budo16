
UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"blog_general"}'
WHERE `name` = 'core_main_blog';

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"blog_general"}'
WHERE `name` = 'core_sitemap_blog';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('blog_main_browse', 'blog', 'Browse Entries', 'Blog_Plugin_Menus::canViewBlogs', '{"route":"blog_general"}', 'blog_main', '', 1),
('blog_main_manage', 'blog', 'My Entries', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"manage"}', 'blog_main', '', 2),
('blog_main_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"create"}', 'blog_main', '', 3),

('blog_quick_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus::canCreateBlogs', '{"route":"blog_general","action":"create","class":"buttonlink icon_blog_new"}', 'blog_quick', '', 1),
('blog_quick_style', 'blog', 'Edit Blog Style', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"style","class":"smoothbox buttonlink icon_blog_style"}', 'blog_quick', '', 2),

('blog_gutter_list', 'blog', 'View All Entries', 'Blog_Plugin_Menus', '{"route":"blog_view","class":"buttonlink icon_blog_viewall"}', 'blog_gutter', '', 1),
('blog_gutter_create', 'blog', 'Write New Entry', 'Blog_Plugin_Menus', '{"route":"blog_general","action":"create","class":"buttonlink icon_blog_new"}', 'blog_gutter', '', 2),
('blog_gutter_edit', 'blog', 'Edit This Entry', 'Blog_Plugin_Menus', '{"route":"blog_specific","action":"edit","class":"buttonlink icon_blog_edit"}', 'blog_gutter', '', 3),
('blog_gutter_delete', 'blog', 'Delete This Entry', 'Blog_Plugin_Menus', '{"route":"blog_specific","action":"delete","class":"buttonlink icon_blog_delete"}', 'blog_gutter', '', 4)
;

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('blog_main', 'standard', 'Blog Main Navigation Menu'),
('blog_quick', 'standard', 'Blog Quick Navigation Menu'),
('blog_gutter', 'standard', 'Blog Gutter Navigation Menu')
;
