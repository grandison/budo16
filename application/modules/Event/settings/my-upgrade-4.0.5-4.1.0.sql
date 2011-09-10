
UPDATE `engine4_activity_notificationtypes`
SET `body` = '{item:$subject} has requested to join the event {item:$object}.'
WHERE `type` = 'event_approve' ;


INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Event Privacy', 'event_maintenance_rebuild_privacy', 'event', 'Event_Plugin_Job_Maintenance_RebuildPrivacy', 50);


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('event_quick_create', 'event', 'Create New Event', 'Event_Plugin_Menus::canCreateEvents', '{"route":"event_general","action":"create","class":"buttonlink icon_event_new"}', 'event_quick', '', 1);

-- Corrects "active" class on core_main menu
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"event_general","action":"browse"}' WHERE `name` = 'core_main_event' LIMIT 1;