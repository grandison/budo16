
/* insert menus */

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('poll_main', 'standard', 'Poll Main Navigation Menu'),
('poll_quick', 'standard', 'Poll Quick Navigation Menu')
;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"poll_general","action":"browse"}'
WHERE `name` = 'core_main_poll' ;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"poll_general","action":"browse"}'
WHERE `name` = 'core_sitemap_poll' ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('poll_main_browse', 'poll', 'Browse Polls', 'Poll_Plugin_Menus::canViewPolls', '{"route":"poll_general","action":"browse"}', 'poll_main', '', 1),
('poll_main_manage', 'poll', 'My Polls', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"manage"}', 'poll_main', '', 2),
('poll_main_create', 'poll', 'Create New Poll', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"create"}', 'poll_main', '', 3),

('poll_quick_create', 'poll', 'Create New Poll', 'Poll_Plugin_Menus::canCreatePolls', '{"route":"poll_general","action":"create","class":"buttonlink icon_poll_new"}', 'poll_quick', '', 1)
;



/* update auth */

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'vote' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'poll' as `type`,
    'vote' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'poll' as `resource_type`,
    poll_id as `resource_id`,
    'vote' as `action`,
    'registered' as `role`,
    0 as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_poll_polls`
) ;
