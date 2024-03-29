<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8474 2011-02-16 01:33:15Z john $
 * @author     John
 */
return array(
  '4.1.2' => array(
    'controllers/AdminManageController.php' => 'Fixed commenting issue with feed items',
    'externals/styles/main.css' => 'Different',
    'Form/Topic/Create.php' => 'Title maximum length set to 64',
    'Form/Topic/Rename.php' => 'Title maximum length set to 64',
    'Model/Post.php' => 'Fixed commenting issue with feed items',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added preliminary layout editor enhancements',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/add-moderator.tpl' => 'Fixed IE issue with adding a moderator',
    'views/scripts/topic/view.tpl' => 'Added CSS classes to owner posts',
  ),
  '4.1.1' => array(
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'Model/Forum.php' => 'Fixed issue with changing order',
    'Model/Post.php' => 'Changes for storage system modifications',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.0' => array(
    '/application/languages/en/forum.csv' => 'Fixed phrases with stray double-quotes',
    'controllers/AdminLevelController.php' => 'Added notice on form save',
    'controllers/AdminManageController.php' => 'Fixed issue with adding a moderator',
    'controllers/AdminSettingsController.php' => 'Added notice on form save',
    'controllers/ForumController.php' => 'Fixed issue where topic page quick links were incorrect',
    'controllers/PostController.php' => 'Fixed issues with HTML and line breaks',
    'controllers/TopicController.php' => 'Fixed issues with HTML and line breaks; added scroll to post when link goes directly to post',
    'externals/images/nophoto_forum_thumb_icon.png' => 'Updated',
    'externals/images/nophoto_post_thumb_icon.png' => 'Updated',
    'externals/images/nophoto_topic_thumb_icon.png' => 'Updated',
    'externals/images/post/move.png' => 'Added',
    'externals/images/promote.png' => 'Added',
    'externals/styles/main.css' => 'Added styles',
    'Form/Post/Create.php' => 'Fixed issues with HTML and line breaks',
    'Form/Post/Edit.php' => 'Fixed issues with HTML and line breaks',
    'Model/Category.php' => 'Fixed admin ordering oddities',
    'Model/Forum.php' => 'Fixed admin ordering oddities',
    'Model/Post.php' => 'Fixed b/c issue with posts from v3',
    'Model/Topic.php' => 'Fixed privacy issue',
    'Plugin/Core.php' => 'Fixed issue with activity feed items',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added pagination/item count limits to widgets',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.5p1-4.1.0.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/forum/view.tpl' => 'UI improvements; fixed issue with alternating background color in IE8',
    'views/scripts/topic/view.tpl' => 'UI improvements; fixed issue with automatic link enabling; added scroll to post when link goes directly to post; added report option to posts; fixed issue with alternating background color in IE8',
    'widgets/profile-forum-posts/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-forum-posts/index.tpl' => 'Added pagination/item count limit',
    'widgets/profile-forum-topics/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-forum-topics/index.tpl' => 'Added pagination/item count limit',
  ),
  '4.0.5p1' => array(
    'externals/images/nophoto_post_thumb_icon.png' => 'Added',
    'externals/images/nophoto_topic_thumb_icon.png' => 'Added',
    'settings/manifest.php' => 'Incremented version',
  ),
  '4.0.5' => array(
    'controllers/AdminLevelController.php' => 'Added several new settings',
    'controllers/AdminManageController.php' => 'Added ability to restrict a forum to a user level; fixed issues with authorization',
    'controllers/AdminSettingsController.php' => 'Renamed form',
    'controllers/CategoryController.php' => 'Removed deprecated code',
    'controllers/ForumController.php' => 'Improved authorization handling; added topic watching support',
    'controllers/IndexController.php' => 'Added ability to restrict a forum to a user level',
    'controllers/PostController.php' => 'Improved authorization handling; added topic watching support',
    'controllers/TopicController.php' => 'Improved authorization handling; added topic watching support',
    'externals/images/nophoto_forum_thumb_icon.png' => 'Added',
    'externals/images/unwatch.png' => 'Added',
    'externals/images/watch.png' => 'Added',
    'externals/styles/admin/main.css' => 'Added styles',
    'externals/styles/main.css' => 'Added styles',
    'Form/Admin/AddCategory.php' => 'Removed',
    'Form/Admin/AddForum.php' => 'Removed',
    'Form/Admin/AddModerator.php' => 'Removed',
    'Form/Admin/Category/Create.php' => 'Added',
    'Form/Admin/Category/Delete.php' => 'Added',
    'Form/Admin/Category/Edit.php' => 'Added',
    'Form/Admin/DeleteCategory.php' => 'Removed',
    'Form/Admin/DeleteForum.php' => 'Removed',
    'Form/Admin/EditCategory.php' => 'Removed',
    'Form/Admin/EditForum.php' => 'Removed',
    'Form/Admin/Forum/Create.php' => 'Added',
    'Form/Admin/Forum/Delete.php' => 'Added',
    'Form/Admin/Forum/Edit.php' => 'Added',
    'Form/Admin/Global.php' => 'Removed',
    'Form/Admin/Moderator/Create.php' => 'Added',
    'Form/Admin/Moderator/Delete.php' => 'Added',
    'Form/Admin/RemoveModerator.php' => 'Removed',
    'Form/Admin/Settings/Global.php' => 'Added',
    'Form/Admin/Settings/Level.php' => 'Added several new settings',
    'Form/Category/Create.php' => 'Code formatting',
    'Form/Category/Delete.php' => 'Code formatting',
    'Form/Category/Edit.php' => 'Code formatting',
    'Form/Forum/Create.php' => 'Added ability to restrict a forum to a user level',
    'Form/Forum/Delete.php' => 'Added ability to restrict a forum to a user level',
    'Form/Forum/Edit.php' => 'Added ability to restrict a forum to a user level',
    'Form/Post/Create.php' => 'Added topic watching support',
    'Form/Post/Quick.php' => 'Added topic watching support',
    'Form/Topic/Create.php' => 'Added topic watching support',
    'Form/Topic/Delete.php' => 'Code formatting',
    'Form/Topic/Move.php' => 'Added',
    'Form/Topic/Rename.php' => 'Code formatting',
    'Model/DbTable/TopicWatches.php' => 'Added',
    'Model/Forum.php' => 'Added URL slugs',
    'Model/Post.php' => 'Added URL slugs; fixed issues with updating forum last post and counts on deletion',
    'Model/Topic.php' => 'Added URL slugs; fixed issues with updating forum last post and counts on deletion',
    'Plugin/Core.php' => 'Merged from Forum_Plugin_User',
    'Plugin/User.php' => 'Removed',
    'settings/changelog.php' => 'Added',
    'settings/content.php' => 'Added',
    'settings/install.php' => 'Added tabs to profile',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.4-4.0.5.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/add-category.tpl' => 'Code formatting',
    'views/scripts/admin-manage/add-forum.tpl' => 'Code formatting',
    'views/scripts/admin-manage/add-moderator.tpl' => 'Code formatting',
    'views/scripts/admin-manage/edit-category.tpl' => 'Code formatting',
    'views/scripts/category/create.tpl' => 'Removed',
    'views/scripts/category/view.tpl' => 'Removed',
    'views/scripts/forum/create.tpl' => 'Removed',
    'views/scripts/forum/topic-create.tpl' => 'Added',
    'views/scripts/forum/view.tpl' => 'Improved authorization handling',
    'views/scripts/index/index.tpl' => 'Code formatting',
    'views/scripts/topic/create.tpl' => 'Removed',
    'views/scripts/topic/move.tpl' => 'Added',
    'views/scripts/topic/post-create.tpl' => 'Added',
    'views/scripts/topic/view.tpl' => 'Improved authorization handling; added topic watching support',
    'widgets/list-recent-posts/Controller.php' => 'Added',
    'widgets/list-recent-posts/index.tpl' => 'Added',
    'widgets/list-recent-topics/Controller.php' => 'Added',
    'widgets/list-recent-topics/index.tpl' => 'Added',
    'widgets/profile-forum-posts/Controller.php' => 'Added',
    'widgets/profile-forum-posts/index.tpl' => 'Added',
    'widgets/profile-forum-topics/Controller.php' => 'Added',
    'widgets/profile-forum-topics/index.tpl' => 'Added',
    '/application/languages/en/forum.csv' => 'Added missing phrases',
  ),
  '4.0.4' => array(
    'externals/styles/main.css' => 'Improved RTL support',
    'Model/Category.php' => 'Fixed incorrect link in search results',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    '/application/languages/en/forum.csv' => 'Added missing phrases',
  ),
  '4.0.3' => array(
    'controllers/AdminLevelController.php' => 'Fixed bug preventing changing of level',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-level/index.tpl' => 'Fixed bug preventing changing of level',
    'views/scripts/forum/view.tpl' => 'Added missing translation',
    'views/scripts/index/index.tpl' => 'Added missing translation',
    '/application/languages/en/forum.csv' => 'Added phrases',
  ),
  '4.0.2' => array(
    'controllers/AdminLevelController.php' => 'Form adjustments',
    'controllers/PostController.php' => 'Adding permission checking to editing/deleting posts',
    'Form/Admin/Level.php' => 'Moved',
    'Form/Admin/Settings/Level.php' => 'Various level settings fixes and enhancements',
    'Model/Topic.php' => 'Fixes bug when the last post is deleted in a topic',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.1-4.0.2.sql' => 'Added',
    'settings/my.sql' => 'Various level settings fixes and enhancements; fixed problem that would prevent post editing and deleting',
    'views/scripts/index/index.tpl' => 'Last post optimization',
    'views/scripts/topic/view.tpl' => 'Fixes edit auth problem; fixed missing signature problem',
  ),
  '4.0.1' => array(
    'controllers/AdminLevelController.php' => 'Fixed problem in level select',
    'controllers/ForumController.php' => 'Added view count support',
    'controllers/TopicController.php' => 'Added missing level permission check for quick reply',
    'Form/Post/Quick.php' => 'Added label to quick reply form',
    'Model/Forum.php' => 'Added lastpost_id support',
    'Model/Post.php' => 'Better cleanup of temporary files; added forum lastpost_id support',
    'Model/Topic.php' => 'Added lastpost_id support',
    'Plugin/Core.php' => 'Query optimization',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.0-4.0.1.sql' => 'Added',
    'settings/my.sql' => 'Added lastposter_id and view_count columns to the engine4_forum_forums table; fixed incorrect primary key on the engine4_forum_listitems table; added lastpost_id and lastposter_id columns to the engine4_forum_topics table',
  ),
) ?>