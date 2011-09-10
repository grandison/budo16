
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Group Privacy', 'group_maintenance_rebuild_privacy', 'group', 'Group_Plugin_Job_Maintenance_RebuildPrivacy', 50);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('group_quick_create', 'group', 'Create New Group', 'Group_Plugin_Menus::canCreateGroups', '{"route":"group_general","action":"create","class":"buttonlink icon_group_new"}', 'group_quick', '', 1);

-- Corrects "active" class on core_main menu
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"group_general","action":"browse"}' WHERE `name` = 'core_main_group' LIMIT 1;

-- moderator/admin
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo.edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'topic.edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- users
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'event' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'photo.edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'topic.edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'event' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'group' as `type`,
    'auth_event' as `name`,
    5 as `value`,
    '["registered", "member", "officer"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- elevate permissions for existing group officers
INSERT IGNORE INTO `engine4_authorization_allow`
  SELECT
    'group' as 'resource_type',
    owner_id as `resource_id`,
    'topic.edit' as `action`,
    'group_list' as `role`,
    list_id as role_id,
    1 as `value`,
    NULL as `params`
  FROM `engine4_group_lists`
;
INSERT IGNORE INTO `engine4_authorization_allow`
  SELECT
    'group' as 'resource_type',
    owner_id as `resource_id`,
    'photo.edit' as `action`,
    'group_list' as `role`,
    list_id as role_id,
    1 as `value`,
    NULL as `params`
  FROM `engine4_group_lists`
;