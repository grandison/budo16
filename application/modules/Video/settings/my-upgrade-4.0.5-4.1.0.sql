
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `enabled`, `multi`, `priority`) VALUES
('Video Encode', 'video_encode', 'video', 'Video_Plugin_Job_Encode', 1, 2, 75),
('Rebuild Video Privacy', 'video_maintenance_rebuild_privacy', 'video', 'Video_Plugin_Job_Maintenance_RebuildPrivacy', 1, 1, 50);

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"video","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_video' LIMIT 1;

-- Corrects "active" class on core_main menu
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"video_general","action":"browse"}' WHERE `name` = 'core_main_video' LIMIT 1;