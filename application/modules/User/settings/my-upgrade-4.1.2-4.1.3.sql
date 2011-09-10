
ALTER TABLE `engine4_users`
  ADD COLUMN `approved` tinyint(1) NOT NULL default '1' AFTER `verified` ;
