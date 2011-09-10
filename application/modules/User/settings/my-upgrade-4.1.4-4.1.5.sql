UPDATE `engine4_users` SET `modified_date` = `creation_date` WHERE `modified_date` IS NULL;
UPDATE `engine4_users` SET `lastlogin_date` = `modified_date` WHERE `lastlogin_date` IS NULL;
