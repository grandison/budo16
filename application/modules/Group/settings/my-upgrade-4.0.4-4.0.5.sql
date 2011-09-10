
/* update incorrect menu item */
UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"admin_default","module":"group","controller":"settings","action":"level"}'
WHERE `name` = 'group_admin_main_level' ;


/* add topic watches */
DROP TABLE IF EXISTS `engine4_group_topicwatches`;
CREATE TABLE IF NOT EXISTS `engine4_group_topicwatches` (
  `resource_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `watch` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`resource_id`,`topic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT IGNORE INTO `engine4_group_topicwatches` (
  SELECT DISTINCT
    `group_id` as `resource_id`,
    `topic_id` as `topic_id`,
    `user_id` as `user_id`,
    1 as `watch`
  FROM
    `engine4_group_posts`
) ;


/* fix incorrect params for group approve */
UPDATE `engine4_activity_notificationtypes`
SET `body` = '{item:$subject} has requested to join the group {item:$object}.'
WHERE `type` = 'group_approve' ;
