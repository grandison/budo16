
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

/* insert allow */
INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    forum_id as `resource_id`,
    'comment' as `action`,
    'registered' as `role`,
    0 as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_forums`
);
