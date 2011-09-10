
ALTER TABLE `engine4_users`
  CHANGE COLUMN `username` `username` varchar(128) default NULL ;

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('user.signup.username', 1);

/* Fix some incorrect default collations */
ALTER TABLE `engine4_user_fields_maps` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
ALTER TABLE `engine4_user_online` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
