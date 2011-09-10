ALTER TABLE  `engine4_core_mail`
  ADD  `recipient_total` INT( 10 ) NOT NULL AFTER  `recipient_count` ,
  ADD  `creation_time` DATETIME NOT NULL AFTER  `recipient_total`;

/* Moves core_admin_message_mail to sit just below "Announcements" */
UPDATE `engine4_core_menuitems` SET `order` = `order`+1 WHERE `menu` = 'core_admin_main_manage' AND `order` > 4;

INSERT INTO `engine4_core_menuitems` (`name`, `module` ,`label` ,`plugin` ,`params` ,`menu` ,`submenu` ,`custom` ,`order`)
  VALUES
  ('core_admin_message_mail',  'core',  'Email All Members',  '',  '{"route":"admin_default","module":"core","controller":"message","action":"mail"}',  'core_admin_main_manage',  '',  '0',  '5');
