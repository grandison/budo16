
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_notifications', 'user', 'Notifications', '', '{"route":"user_extended","module":"user","controller":"settings","action":"notifications"}', 'user_settings', '', 2)
;

/* This is part of a fix for a bug that causes email alerts to be disabled if not in certain modules */
DELETE `engine4_activity_notificationsettings`.*
FROM `engine4_activity_notificationsettings`
LEFT JOIN `engine4_activity_notificationtypes`
ON `engine4_activity_notificationsettings`.`type`=`engine4_activity_notificationtypes`.`type`
WHERE `engine4_activity_notificationtypes`.`module` NOT IN('user', 'activity', 'messages');
