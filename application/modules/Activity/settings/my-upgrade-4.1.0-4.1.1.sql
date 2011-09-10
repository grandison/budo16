
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Activity Privacy', 'activity_maintenance_rebuild_privacy', 'activity', 'Activity_Plugin_Job_Maintenance_RebuildPrivacy', 50);

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"admin_default","module":"activity","controller":"settings","action":"index"}'
WHERE `name` = 'core_admin_main_settings_activity' ;
