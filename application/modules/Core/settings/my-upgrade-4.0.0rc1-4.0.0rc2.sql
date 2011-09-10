
/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
  DROP COLUMN `executed_last`,
  DROP COLUMN `executed_count`,
  DROP COLUMN `failure_count`,
  DROP COLUMN `success_count`,
  ADD COLUMN `system` tinyint(1) NOT NULL default '0' AFTER `task_id`,
  ADD COLUMN `title` varchar(255) NOT NULL default '' AFTER `task_id`,
  ADD COLUMN `executing` tinyint(1) NOT NULL default '0' AFTER `enabled`,
  ADD COLUMN `executing_id` int(11) unsigned NOT NULL default '0' AFTER `executing`,
  ADD COLUMN `started_last` int(11) NOT NULL default '0',
  ADD COLUMN `started_count` int(11) unsigned NOT NULL default '0',
  ADD COLUMN `completed_last` int(11) NOT NULL default '0',
  ADD COLUMN `completed_count` int(11) unsigned NOT NULL default '0',
  ADD COLUMN `failure_last` int(11) NOT NULL default '0',
  ADD COLUMN `failure_count` int(11) unsigned NOT NULL default '0',
  ADD COLUMN `success_last` int(11) NOT NULL default '0',
  ADD COLUMN `success_count` int(11) unsigned NOT NULL default '0'
;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET title = 'Background Mailer'
WHERE plugin = 'Core_Plugin_Task_Mail';
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET title = 'Statistics'
WHERE plugin = 'Core_Plugin_Task_Statistics';
*/

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.tasks.interval', '60'),
('core.tasks.key', ''),
('core.tasks.last', ''),
('core.tasks.mode', 'curl'),
('core.tasks.pid', ''),
('core.tasks.timeout', '900')
;
