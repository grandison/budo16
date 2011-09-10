
-- Add lastpost_id and lastposter_id
ALTER TABLE `engine4_forum_topics`
  ADD COLUMN `lastpost_id` int(11) unsigned NOT NULL default '0',
  ADD COLUMN `lastposter_id` int(11) unsigned NOT NULL default '0';

UPDATE `engine4_forum_topics` SET
  `lastpost_id` = (SELECT `post_id` FROM `engine4_forum_posts` WHERE `topic_id` = `engine4_forum_topics`.`topic_id` ORDER BY `post_id` DESC LIMIT 1),
  `lastposter_id` = (SELECT `user_id` FROM `engine4_forum_posts` WHERE `topic_id` = `engine4_forum_topics`.`topic_id` ORDER BY `post_id` DESC LIMIT 1)
  ;


-- Add lastposter_id
ALTER TABLE `engine4_forum_forums`
  ADD COLUMN `lastposter_id` int(11) unsigned NOT NULL default '0';

UPDATE `engine4_forum_forums` SET
  `lastposter_id` = (SELECT `user_id` FROM `engine4_forum_posts` WHERE `post_id` = `engine4_forum_forums`.`lastpost_id` LIMIT 1)
  ;


-- Add view_count
ALTER TABLE `engine4_forum_forums`
  ADD COLUMN `view_count` int(11) unsigned NOT NULL default '0' AFTER `file_id`;


-- Add forum_id to posts?
ALTER TABLE `engine4_forum_posts`
  ADD COLUMN `forum_id` int(11) unsigned NOT NULL AFTER `topic_id` ;

UPDATE `engine4_forum_posts` SET
  `forum_id` = (SELECT `forum_id` FROM `engine4_forum_topics` WHERE `topic_id` = `engine4_forum_posts`.`topic_id` LIMIT 1)
  ;


-- Re-create forum listitems table
RENAME TABLE `engine4_forum_listitems` TO `engine4_forum_listitems_bak` ;

CREATE TABLE `engine4_forum_listitems` (
  `listitem_id` int(11) unsigned NOT NULL auto_increment,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT INTO `engine4_forum_listitems`
  SELECT * FROM `engine4_forum_listitems_bak` ;

DROP TABLE `engine4_forum_listitems_bak` ;