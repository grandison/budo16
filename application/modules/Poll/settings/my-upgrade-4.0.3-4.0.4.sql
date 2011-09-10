
/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `timeout`, `type`) VALUES
('Rebuild Poll Privacy', 'rebuild_privacy', 'poll', 'Poll_Plugin_Task_Maintenance_RebuildPrivacy', 0, 'semi-automatic');
*/

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_poll', 'poll', 'Polls', '', '{"route":"admin_default","module":"poll","controller":"settings","action":"level"}', 'authorization_admin_level', '', 999)
;
