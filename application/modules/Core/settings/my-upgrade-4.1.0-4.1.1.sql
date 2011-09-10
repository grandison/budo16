
/* Insert default value for browse members option */
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.general.browse', '1')
;

/*
ALTER TABLE `engine4_core_pages`
  CHANGE COLUMN `levels` `levels` text default NULL AFTER `layout` ;
*/

ALTER TABLE `engine4_core_jobtypes`
  ADD UNIQUE KEY (`type`);

ALTER TABLE `engine4_core_tagmaps`
  ADD COLUMN `creation_date` datetime default NULL ;

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Log Rotation', 'core', 'Core_Plugin_Task_LogRotation', 7200);
