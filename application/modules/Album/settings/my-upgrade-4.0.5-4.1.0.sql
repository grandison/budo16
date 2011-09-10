
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Activity Privacy', 'activity_maintenance_rebuild_privacy', 'activity', 'Activity_Plugin_Job_Maintenance_RebuildPrivacy', 50);

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"album","controller":"manage","action":"index"}' WHERE `name` = 'core_admin_main_plugins_album' LIMIT 1;

-- Fix language in menu item
UPDATE `engine4_core_menuitems` SET `label` = 'Browse Albums' WHERE `name` = 'album_main_browse';