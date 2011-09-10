
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Member Privacy', 'user_maintenance_rebuild_privacy', 'user', 'User_Plugin_Job_Maintenance_RebuildPrivacy', 50);

DELETE FROM `engine4_core_tasks` WHERE `module` = 'user' || `plugin` = 'User_Plugin_Task_Cleanup' ;

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Member Data Maintenance', 'user', 'User_Plugin_Task_Cleanup', 60);

UPDATE `engine4_core_menuitems`
SET `order` = `order` + 1
WHERE `menu` = 'user_settings' && `order` >= 4 && `module` = 'user' ;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'activity' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('user_account_approved', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link]');

UPDATE `engine4_storage_files` SET `parent_type` = 'user' WHERE `parent_type` = 'viewer' ;
