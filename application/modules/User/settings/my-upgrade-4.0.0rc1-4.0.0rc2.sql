
/* This query was removed for changes in 4.1.0 */
/*
INSERT IGNORE INTO `engine4_core_tasks` (`title`, `plugin`, `timeout`, `enabled`) VALUES
('Member Data Maintenance', 'User_Plugin_Task_Cleanup', 60, 1);
*/

DELETE FROM `engine4_core_menuitems` WHERE `name` ='user_admin_main_manage' AND `menu` = 'user_admin_main' LIMIT 1;
DELETE FROM `engine4_core_menuitems` WHERE `name` ='user_admin_main_level' AND `menu` = 'user_admin_main' LIMIT 1;