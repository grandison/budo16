ALTER TABLE `engine4_users`
  CHANGE `password` `password` char(32) COLLATE 'utf8_unicode_ci' NOT NULL,
  CHANGE `salt`     `salt`     char(64) COLLATE 'utf8_unicode_ci' NOT NULL;