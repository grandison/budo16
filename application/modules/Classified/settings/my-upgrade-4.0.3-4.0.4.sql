
/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Classified Privacy', 'rebuild_privacy', 'classified', 'Classified_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

DELETE FROM `engine4_activity_notificationtypes` WHERE `module` = 'classified' ;

DELETE FROM `engine4_activity_notifications` WHERE `type` IN(
  'commented_classified',
  'comment_classified',
  'liked_classified',
  'like_classified'
);

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('classified_main', 'standard', 'Classified Main Navigation Menu'),
('classified_quick', 'standard', 'Classified Quick Navigation Menu'),
('classified_gutter', 'standard', 'Classified Gutter Navigation Menu')
;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"classified_general"}'
WHERE `name` = 'core_main_classified' ;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"classified_general"}'
WHERE `name` = 'core_sitemap_classified' ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('classified_main_browse', 'classified', 'Browse Listings', 'Classified_Plugin_Menus::canViewClassifieds', '{"route":"classified_general"}', 'classified_main', '', 1),
('classified_main_manage', 'classified', 'My Listings', 'Classified_Plugin_Menus::canCreateClassifieds', '{"route":"classified_general","action":"manage"}', 'classified_main', '', 2),
('classified_main_create', 'classified', 'Post a New Listing', 'Classified_Plugin_Menus::canCreateClassifieds', '{"route":"classified_general","action":"create"}', 'classified_main', '', 3),

('classified_quick_create', 'classified', 'Post a New Listing', 'Classified_Plugin_Menus::canCreateClassifieds', '{"route":"classified_general","action":"create","class":"buttonlink icon_classified_new"}', 'classified_quick', '', 1),

('classified_gutter_list', 'classified', 'View All Listings', 'Classified_Plugin_Menus', '{"route":"classified_view","class":"buttonlink icon_classified_viewall"}', 'classified_gutter', '', 1),
('classified_gutter_create', 'classified', 'Post a New Listing', 'Classified_Plugin_Menus', '{"route":"classified_general","action":"create","class":"buttonlink icon_classified_new"}', 'classified_gutter', '', 2),
('classified_gutter_edit', 'classified', 'Edit This Listing', 'Classified_Plugin_Menus', '{"route":"classified_specific","action":"edit","class":"buttonlink icon_classified_edit"}', 'classified_gutter', '', 3),
('classified_gutter_delete', 'classified', 'Delete This Listing', 'Classified_Plugin_Menus', '{"route":"classified_specific","action":"delete","class":"buttonlink icon_classified_delete"}', 'classified_gutter', '', 4)
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_classified', 'classified', 'Classifieds', '', '{"route":"admin_default","module":"classified","controller":"level","action":"index"}', 'authorization_admin_level', '', 999)
;
