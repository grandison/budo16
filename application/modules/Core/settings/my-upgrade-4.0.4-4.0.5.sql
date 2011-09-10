
/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks`
ADD COLUMN `priority` smallint(3) NOT NULL default '50'
AFTER `enabled`;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks` ADD INDEX ( `module` ) ;
*/

/* This query was removed for changes in 4.1.0 */
/*
ALTER TABLE `engine4_core_tasks` ADD INDEX ( `enabled` ) ;
*/

/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `category`, `module`, `plugin`, `system`, `timeout`, `enabled`) VALUES
('Cache Prefetch', 'system', 'core', 'Core_Plugin_Task_Prefetch', 1, 300, 1);
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `priority` = 20 WHERE `category` = 'rebuild_privacy' ;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `priority` = 100 WHERE `plugin` = 'Core_Plugin_Task_Mail' ;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `priority` = 100 WHERE `plugin` = 'Core_Plugin_Task_Statistics' ;
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks` SET `priority` = 200 WHERE `plugin` = 'Core_Plugin_Task_Prefetch' ;
*/

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.tasks.maxjobs', '8'),
('core.tasks.maxtime', '900')
;

DELETE FROM `engine4_core_settings` WHERE `name` IN(
  'activity.notifications.template',
  'authorization.defaultlevel',
  'core.comet.enabled',
  'core.comet.mode',
  'core.comet.delay',
  'core.comet.reconnect'
);

/* Try to fix broken pages */
UPDATE `engine4_core_content`
SET parent_content_id = NULL
WHERE parent_content_id = 0 ;

/* Fix broken status table */
UPDATE `engine4_core_status` SET `resource_type` = 'user' WHERE `resource_type` = '' ;
