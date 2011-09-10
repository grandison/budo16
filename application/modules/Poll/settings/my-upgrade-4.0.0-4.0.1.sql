
ALTER TABLE `engine4_poll_polls` ADD COLUMN
  `comment_count` int(11) unsigned NOT NULL default '0' AFTER `views`;

ALTER TABLE `engine4_poll_polls` ADD COLUMN
  `search` tinyint(1) NOT NULL default '1' AFTER `comment_count`;

ALTER TABLE `engine4_poll_polls` ADD COLUMN
  `vote_count` tinyint(1) NOT NULL default '0' AFTER `comment_count`;

UPDATE `engine4_poll_polls` SET `comment_count` =
  (SELECT COUNT(*) FROM `engine4_core_comments` WHERE `resource_type` = 'poll' && `resource_id` = `engine4_poll_polls`.`poll_id`) ;

UPDATE `engine4_poll_polls` SET `vote_count` =
  (SELECT COUNT(*) FROM `engine4_poll_votes` WHERE `engine4_poll_votes`.`poll_id` = `engine4_poll_polls`.`poll_id`) ;