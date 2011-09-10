
/* create topic watches table */
DROP TABLE IF EXISTS `engine4_forum_topicwatches`;
CREATE TABLE IF NOT EXISTS `engine4_forum_topicwatches` (
  `resource_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `watch` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`resource_id`,`topic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT IGNORE INTO `engine4_forum_topicwatches` (
  SELECT DISTINCT
    `forum_id` as `resource_id`,
    `topic_id` as `topic_id`,
    `user_id` as `user_id`,
    1 as `watch`
  FROM
    `engine4_forum_posts`
) ;


/* Fix missing forum ids */
UPDATE `engine4_forum_posts`
SET `forum_id` = (
  SELECT `forum_id`
  FROM `engine4_forum_topics`
  WHERE `engine4_forum_topics`.`topic_id` = `engine4_forum_posts`.`topic_id`
  LIMIT 1
) ;


/* insert default lists */
INSERT IGNORE INTO `engine4_forum_lists` (
  SELECT
    NULL as `list_id`,
    forum_id as `owner_id`,
    0 as `child_count`
  FROM
    `engine4_forum_forums`
);


/* add mail templates */
INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_forum_topic_reply', 'forum', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_forum_topic_response', 'forum', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_forum_promote', 'forum', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


/* update menu items */
UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"forum_general"}'
WHERE `name` = 'core_main_forum' ;

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"forum_general"}'
WHERE `name` = 'core_sitemap_forum' ;


/* add action types */
INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('forum_promote', 'forum', '{item:$subject} has been made a moderator for the forum {item:$object}', 1, 3, 1, 1, 1, 1),
('forum_topic_create', 'forum', '{item:$subject} posted a {item:$object:topic} in the forum {itemParent:$object:forum}: {body:$body}', 1, 3, 1, 1, 1, 1),
('forum_topic_reply', 'forum', '{item:$subject} replied to a {item:$object:topic} in the forum {itemParent:$object:forum}: {body:$body}', 1, 3, 1, 1, 1, 1)
;


/* add notification types */
INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('forum_promote', 'forum', 'You were promoted to moderator in the forum {item:$object}.', 0, ''),
('forum_topic_response', 'forum', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::forum topic} you created.', 0, ''),
('forum_topic_reply', 'forum', '{item:$subject} has {item:$object:posted} on a {itemParent:$object::forum topic} you posted on.', 0, '')
;


/* remove old permissions */
DELETE FROM `engine4_authorization_permissions`
WHERE `type` = 'forum' && `name` = 'create' ;

DELETE FROM `engine4_authorization_permissions`
WHERE `type` = 'forum' && `name` = 'edit' ;

DELETE FROM `engine4_authorization_permissions`
WHERE `type` = 'forum' && `name` = 'delete' ;

DELETE FROM `engine4_authorization_permissions`
WHERE `type` = 'forum' && `name` = 'moderate' ;

/* remove old allow */
DELETE FROM `engine4_authorization_allow`
WHERE `resource_type` = 'forum' && `role` = 'forum_list' ;

/* insert allow */
INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    forum_id as `resource_id`,
    'view' as `action`,
    'everyone' as `role`,
    0 as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_forums`
);

INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    forum_id as `resource_id`,
    'topic.create' as `action`,
    'registered' as `role`,
    0 as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_forums`
);

INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    forum_id as `resource_id`,
    'post.create' as `action`,
    'registered' as `role`,
    0 as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_forums`
);


/* insert allow permissions - lists */
INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    owner_id as `resource_id`,
    'topic.edit' as `action`,
    'forum_list' as `role`,
    list_id as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_lists`
);

INSERT IGNORE INTO `engine4_authorization_allow` (
  SELECT
    'forum' as `resource_type`,
    owner_id as `resource_id`,
    'topic.delete' as `action`,
    'forum_list' as `role`,
    list_id as `role_id`,
    1 as `value`,
    NULL as `params`
  FROM
    `engine4_forum_lists`
);


/* insert permissions - general */
-- ADMIN
-- forum
-- create, view, edit, delete, topic.create
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'post.create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- ADMIN
-- forum_topic
-- create, edit, delete, move, post.create
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'move' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- ADMIN
-- forum_post
-- create, edit, delete
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- forum
-- view, topic.create
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'topic.delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'post.create' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- USER
-- forum_topic
-- create, edit, delete, post.create
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_topic' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- USER
-- forum_post
-- create, edit, delete
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum_post' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');

-- ALL
-- commentHtml
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'forum' as `type`,
    'commentHtml' as `name`,
    3 as `value`,
    'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
