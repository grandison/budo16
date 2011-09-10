
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Poll Privacy', 'poll_maintenance_rebuild_privacy', 'poll', 'Poll_Plugin_Job_Maintenance_RebuildPrivacy', 50);

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"poll","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_poll' LIMIT 1;
