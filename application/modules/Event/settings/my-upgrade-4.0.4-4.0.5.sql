
/* request notification type */
UPDATE `engine4_activity_notificationtypes`
SET `is_request` = 1, `handler` = 'event.widget.request-event'
WHERE `type` = 'event_invite' ;


/* add topic watches */
DROP TABLE IF EXISTS `engine4_event_topicwatches`;
CREATE TABLE IF NOT EXISTS `engine4_event_topicwatches` (
  `resource_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `watch` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`resource_id`,`topic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT IGNORE INTO `engine4_event_topicwatches` (
  SELECT DISTINCT
    `event_id` as `resource_id`,
    `topic_id` as `topic_id`,
    `user_id` as `user_id`,
    1 as `watch`
  FROM
    `engine4_event_posts`
) ;
