
DELETE FROM `engine4_core_search` WHERE `type` = 'authorization_level';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_main', 'authorization', 'Level Info', '', '{"route":"admin_default","module":"authorization","controller":"level","action":"edit"}', 'authorization_admin_level', '', 1)
;
