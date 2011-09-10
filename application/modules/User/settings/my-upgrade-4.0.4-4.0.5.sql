
/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET `module` = 'user'
WHERE `plugin` = 'User_Plugin_Task_Cleanup';
*/

/* This query was modified for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET `priority` = 60
WHERE `plugin` = 'User_Plugin_Task_Cleanup' ;
*/

ALTER TABLE `engine4_user_fields_meta`
ADD COLUMN `show` tinyint(1) unsigned NOT NULL default '1'
AFTER `search` ;