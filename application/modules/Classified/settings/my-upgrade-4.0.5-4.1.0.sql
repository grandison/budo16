
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Classified Privacy', 'classified_maintenance_rebuild_privacy', 'classified', 'Classified_Plugin_Job_Maintenance_RebuildPrivacy', 50);

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"classified_specific","action":"delete","class":"buttonlink smoothbox icon_classified_delete"}' WHERE `name` = 'classified_gutter_delete' LIMIT 1;

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"classified","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_classified' LIMIT 1;

-- Delete gutter menu item? Harm in leaving it?
DELETE FROM `engine4_core_menus` WHERE `name` = 'classified_gutter';
DELETE FROM `engine4_core_menuitems` WHERE `name` = 'classified_gutter_list';
DELETE FROM `engine4_core_menuitems` WHERE `name` = 'classified_gutter_create';
DELETE FROM `engine4_core_menuitems` WHERE `name` = 'classified_gutter_edit';
DELETE FROM `engine4_core_menuitems` WHERE `name` = 'classified_gutter_delete';