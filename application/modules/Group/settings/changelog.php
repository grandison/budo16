<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8474 2011-02-16 01:33:15Z john $
 * @author     John
 */
return array(
  '4.1.2' => array(
    'controllers/IndexController.php' => 'Group category options are now alphabetically ordered in the browse page',
    'controllers/MemberController.php' => 'Fixed issue of trying to rejoin a group through invitation',
    'controllers/TopicController.php' => 'Fixed issue where topic owners cannot use the topic options',
    'externals/styles/main.css' => 'Added styles',
    'Plugin/Menus.php' => 'Adjusted message link',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added widgets',
    'settings/install.php' => 'Added preliminary layout editor enhancements',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/topic/view.tpl' => 'Added CSS class for owner and officer posts',
    'widgets/list-popular-groups/Controller.php' => 'Added',
    'widgets/list-popular-groups/index.tpl' => 'Added',
    'widgets/list-recent-groups/Controller.php' => 'Added',
    'widgets/list-recent-groups/index.tpl' => 'Added',
  ),
  '4.1.1' => array(
    'controllers/MemberController.php' => 'Fixed issue where notification would remain for admin when request had been rejected',
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'externals/styles/main.css' => 'RTL improvements',
    'Model/Photo.php' => 'Changes for storage system modifications',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/event/*' => 'Removed',
  ),
  '4.1.0' => array(
    '/application/languages/en/group.csv' => 'Fixed phrases with stray double-quotes',
    'controllers/AdminManageController.php' => 'Code formatting',
    'controllers/AdminSettingsController.php' => 'Added notice on form save',
    'controllers/GroupController.php' => 'Added event permission options; fixed issue with saving privacy',
    'controllers/IndexController.php' => 'Added navigation to menu system; added event permission options;',
    'controllers/MemberController.php' => 'Language improvement; fixed issue with removing officer status when they leave the group; fixed issue with incorrect parameters to activity feed items',
    'controllers/PhotoController.php' => 'Fixed missing privacy checks for viewing photos',
    'controllers/PostController.php' => 'Fixed issue prevent moderators from editing or deleting posts',
    'controllers/ProfileController.php' => 'Silencing notices caused by content system',
    'controllers/TopicController.php' => 'Fixed issue prevent moderators from editing or deleting topics',
    'externals/images/nophoto_post_thumb_icon.png' => 'Added',
    'externals/images/nophoto_topic_thumb_icon.png' => 'Added',
    'externals/styles/main.css' => 'Added styles',
    'Form/Admin/Settings/Level.php' => 'Added event permission options',
    'Form/Create.php' => 'Fixed issue with disabled privacy options; added event permission options',
    'Form/Delete.php' => 'Fixed UI issues',
    'Form/Edit.php' => 'Fixed issue with disabled privacy options; added event permission options',
    'Form/Topic/Create.php' => 'Automatic link enabling moved to javascript',
    'Model/Photo.php' => 'Fixed moderation permission issues',
    'Model/Topic.php' => 'Fixed moderation permission issues',
    'Plugin/Core.php' => 'Fixed issue with activity feed items',
    'Plugin/Job/Maintenance/RebuildPrivacy.php' => 'Added',
    'Plugin/Menus.php' => 'Added navigation to menu system',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Removed',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added pagination/item count limits to widgets',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Backwards compatibility fix for tasks modifications',
    'settings/my-upgrade-4.0.5-4.1.0.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/index.tpl' => 'Fixed issues with deletion',
    'views/scripts/group/delete.tpl' => 'UI improvements',
    'views/scripts/index/browse.tpl' => 'Added navigation to menu system; UI improvements',
    'views/scripts/index/manage.tpl' => 'Added navigation to menu system; UI improvements',
    'views/scripts/member/invite.tpl' => 'UI improvements',
    'views/scripts/photo/upload.tpl' => 'UI improvements',
    'views/scripts/photo/view.tpl' => 'UI improvements',
    'views/scripts/topic/view.tpl' => 'UI improvements; fixes issue with automatically enabling links',
    'widgets/profile-discussions/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-events/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-events/index.tpl' => 'Added pagination/item count limit',
    'widgets/profile-groups/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-groups/index.tpl' => 'Added pagination/item count limit',
    'widgets/profile-members/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-members/index.tpl' => 'Added pagination/item count limit',
    'widgets/profile-photos/Controller.php' => 'Added pagination/item count limit',
  ),
  '4.0.5' => array(
    'controllers/MemberController.php' => 'Added support for missing action and notification types; join form now shows RSVP options',
    'controllers/TopicController.php' => 'Added topic watching support',
    'externals/images/nophoto_group_thumb_icon.png' => 'Changed',
    'externals/images/unwatch.png' => 'Added',
    'externals/images/watch.png' => 'Added',
    'externals/styles/main.css' => 'Added styles',
    'Form/Admin/Settings/Level.php' => 'Fixed typo in option labels',
    'Form/Create.php' => 'Added missing .jpeg extension to allowed file types',
    'Form/Edit.php' => 'Added missing .jpeg extension to allowed file types',
    'Form/Post/Create.php' => 'Added topic watching support',
    'Form/Post/Edit.php' => 'Added topic watching support',
    'Form/Topic/Create.php' => 'Added topic watching support',
    'Model/DbTable/TopicWatches.php' => 'Added',
    'Model/Group.php' => 'Fixed incorrect argument to getParent function',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Added idle support',
    'settings/changelog.php' => 'Added',
    'settings/install.php' => 'Code formatting',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.4-4.0.5.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/topic/view.tpl' => 'Added topic watching support',
    '/application/languages/en/group.csv' => 'Added missing phrases',
  ),
  '4.0.4' => array(
    'Api/Core.php' => 'Added category ordering',
    'controllers/TopicController.php' => 'Improving auth checking',
    'externals/styles/main.css' => 'Improved RTL support',
    'Form/Post/Delete.php' => 'Style tweak',
    'Form/Post/Edit.php' => 'Style tweak',
    'Form/Topic/Delete.php' => 'Style tweak',
    'Form/Topic/Rename.php' => 'Style tweak',
    'Plugin/Core.php' => 'Fixed issues with privacy in the feed when content is hidden from the public by admin settings',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Added to fix privacy issues in the feed',
    'settings/install.php' => 'Group page should not be considered a custom page',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Added; fixed typo in permissions table leftover from several versions ago; fixed custom page setting',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/photo/view.tpl' => 'Added missing translation',
    'widgets/profile-discussions/*' => 'Added correct auth checking for showing link',
    'widgets/profile-events/index.tpl' => 'Style tweak',
    '/application/languages/en/group.csv' => 'Added phrases',
  ),
  '4.0.3' => array(
    'controllers/GroupController.php' => 'Fixed bug in privacy for officers',
    'Model/Photo.php' => 'Fixed errors when deleting a photo with a missing file',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/index.tpl' => 'Added correct locale date format',
    'widgets/profile-groups/index.tpl' => 'Added missing translation',
    'widgets/profile-info/index.tpl' => 'Added missing translation',
    '/application/languages/en/group.csv' => 'Added phrases',
  ),
  '4.0.2' => array(
    'Api/Core.php' => 'Categories ordered by name',
    'controllers/AdminSettingsController.php' => 'Various level settings fixes and enhancements',
    'controllers/EventController.php' => 'Remove, no longer used',
    'controllers/GroupController.php' => 'Various level settings fixes and enhancements',
    'controllers/IndexController.php' => 'Various level settings fixes and enhancements',
    'externals/styles/main.css' => 'Styled the group events profile tab',
    'Form/Create.php' => 'Various level settings fixes and enhancements',
    'Form/Edit.php' => 'Various level settings fixes and enhancements',
    'Form/Admin/Level.php' => 'Moved',
    'Form/Admin/Settings/Level.php' => 'Various level settings fixes and enhancements',
    'Plugin/Core.php' => 'Added activity stream index type',
    'Plugin/Menus.php' => 'Fixed problem that would prevent the invite link from showing for non-officers when allowed',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.1-4.0.2.sql' => 'Added',
    'settings/my.sql' => 'Various level settings fixes and enhancements',
    'widgets/profile-events/Controller.php' => 'Tab now hides properly when the event plugin is not enabled',
    'widgets/profile-events/index.tpl' => 'Cleanup',
    'widgets/profile-groups/Controller.php' => 'Tab now shows properly when the event plugin is not enabled',
    'widgets/profile-members/index.tpl' => 'Search input text and owner/officer now translate',
  ),
  '4.0.1' => array(
    'Api/Core.php' => 'Better cleanup of temporary files',
    'controllers/AdminSettingsController.php' => 'Fixed problem in level select',
    'controllers/GroupController.php' => 'Added level permission for styles',
    'controllers/IndexController.php' => 'Added menu for group manage page',
    'controllers/PhotoController.php' => 'Added view count support',
    'controllers/TopicController.php' => 'Added view count support',
    'Form/Admin/Level.php' => 'Added level permission for styles',
    'Model/Group.php' => 'Better cleanup of temporary files',
    'Plugin/Core.php' => 'Query optimization',
    'Plugin/Menus.php' => 'Added level permission for styles',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.0-4.0.1.sql' => 'Added',
    'settings/my.sql' => 'Added view_count and comment_count columns to engine4_group_photos table; added view_count column to engine4_group_topics table; added default permissions for style permission',
    'widgets/profile-info/index.tpl' => 'Fixed possible bug when owner title is not set',
  ),
) ?>