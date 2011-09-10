ALTER TABLE `engine4_activity_notificationtypes` ADD `default` tinyint NOT NULL DEFAULT '1' AFTER `handler`;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_notifications', 'activity', 'Default Email Notifications', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"notifications"}', 'core_admin_main_settings', '', 11)
;