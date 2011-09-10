
ALTER TABLE `engine4_core_menuitems` CHANGE COLUMN
  `plugin` `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NULL;

ALTER TABLE `engine4_core_menuitems` ADD COLUMN
  `enabled` tinyint(1) NOT NULL default '1';

UPDATE `engine4_core_settings`
  SET `name` = 'core.mail.from'
  WHERE `name` = 'core.email.from';

UPDATE `engine4_core_settings`
  SET `name` = 'core.mail.name'
  WHERE `name` = 'core.email.name';

DELETE FROM `engine4_core_menuitems` WHERE `name` = 'core_admin_main_settings_email';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_mailtemplates', 'core', 'Mail Templates', '', '{"route":"admin_default","controller":"mail","action":"templates"}', 'core_admin_main_settings', '', 6),
('core_admin_main_settings_mailsettings', 'core', 'Mail Settings', '', '{"route":"admin_default","controller":"mail","action":"settings"}', 'core_admin_main_settings', '', 7)
;