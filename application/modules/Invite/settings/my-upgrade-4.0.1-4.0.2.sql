
INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('invite', 'invite', '[host],[email],[sender_email],[sender_title],[sender_link],[sender_photo],[message],[object_link],[code]'),
('invite_code', 'invite', '[host],[email],[sender_email],[sender_title],[sender_link],[sender_photo],[message],[object_link],[code]');

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('user_home', 'standard', 'Member Home Quick Links Menu'),
('user_profile', 'standard', 'Member Profile Options Menu'),
('user_edit', 'standard', 'Member Edit Profile Navigation Menu'),
('user_settings', 'standard', 'Member Settings Navigation Menu')
;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`, `enabled`) VALUES
('core_main_invite', 'invite', 'Invite', 'Invite_Plugin_Menus::canInvite', '{"route":"default","module":"invite"}', 'core_main', '', 1, 0),
('user_home_invite', 'invite', 'Invite Your Friends', 'Invite_Plugin_Menus::canInvite', '{"route":"default","module":"invite","icon":"application/modules/Invite/externals/images/invite.png"}', 'user_home', '', 5, 0)
;
