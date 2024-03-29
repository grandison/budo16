<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8943 2011-05-16 21:52:41Z john $
 * @author     John
 */
return array(
  '4.1.5' => array(
    'controllers/AdminManageController.php' => 'Fixed pagination issue',
    'controllers/SignupController.php' => 'Fixed issue with members being disabled after signup when payment is enabled',
    'Form/Login.php' => 'Added autofocus to email field (HTML5)',
    'Form/Signup/Account.php' => 'Fixed language list display issue',
    'Model/User.php' => 'Fixed error with default timezones when new members are created',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.4-4.1.5.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/friends/request-follow.tpl' => 'Fixed escaping issue',
    'views/scripts/friends/request-friend.tpl' => 'Fixed escaping issue',
  ),
  '4.1.4' => array(
    '/application/languages/en/user.csv' => 'Added phrases',
    'controllers/AdminLoginsController.php' => 'Added',
    'controllers/AdminManageController.php' => 'Fixed issue with editing a member',
    'controllers/AdminSignupController.php' => 'Fixed issue with editing signup steps',
    'controllers/AuthController.php' => 'Added login tracking (viewable under Banning & Spam)',
    'controllers/SettingsController.php' => 'Fixes issue with updating displayname when profile address can be changed; Members can now leave hidden networks',
    'externals/styles/admin/main.css' => 'Added',
    'externals/styles/main.css' => 'Added svn:keywords Id',
    'externals/styles/mobile.css' => 'Added',
    'Form/Signup/Account.php' => 'Hides language option when there is only one',
    'Model/DbTable/Logins.php' => 'Added',
    'Model/User.php' => 'Fixed error caused when checking if unregistered visitor is an admin; Sets member name as the email is displayname or username is not set',
    'Plugin/Signup/Fields.php' => 'Fixes issue with profile type not being pre-populated when signup order is changed',
    'Plugin/Signup/Photo.php' => 'Step is disabled now for mobile browsers',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.3-4.1.4.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'View/Helper/UserFriendship.php' => 'Fixed issues with friendship link showing up incorrectly in browse members page',
    'views/scripts/admin-logins/clear.tpl' => 'Added',
    'views/scripts/admin-logins/index.tpl' => 'Added',
    'views/scripts/admin-manage/index.tpl' => 'Added missing localization',
    'views/scripts/admin-manage/stats.tpl' => 'Added neutering for demo mode',
    'views/scripts/settings/network.tpl' => 'Fixes network selection issue with Internet Explorer',
    'widgets/profile-friends-followers/index.tpl' => 'Fixes issues with pagaination',
    'widgets/profile-friends-following/index.tpl' => 'Fixes issues with pagaination',
    'widgets/profile-options/Controller.php' => 'Fixes issue with profile-options widget rendering when member is not logged in',
  ),
  '4.1.3' => array(
    '/application/languages/en/user.csv' => 'Added phrases',
    'controllers/AdminManageController.php' => 'Fixed issue with showing a member before paying for a subscription',
    'controllers/AuthController.php' => 'Fixed issue with showing a member before paying for a subscription, Lost password mail template no longer includes the host and scheme in the object_link placeholder. Use http://[host][object_link] to specify resource URL',
    'controllers/FriendsController.php' => 'Fixed issues with one-way friendships',
    'controllers/ProfileController.php' => 'Fixed issue with showing a member before paying for a subscription',
    'controllers/SettingsController.php' => 'Improvements to network settings page',
    'controllers/SignupController.php' => 'Fixed issue with signup activity not showing up for members in the same networks when feed content is set My Friends & Networks',
    'Form/Admin/Manage/Edit.php' => 'Fixed issue with showing a member before paying for a subscription',
    'Form/Settings/Network.php' => 'Improvements to network settings page',
    'Form/Signup/Photo.php' => 'Remove the image size cap for initial signup',
    'Model/DbTable/Membership.php' => 'Fixes rare issue with deleting members',
    'Model/User.php' => 'Fixed issue with showing a member before paying for a subscription',
    'Plugin/Core.php' => 'Fixed issue with showing a member before paying for a subscription',
    'Plugin/Menus.php' => 'Fixed issues with one-way friendships',
    'Plugin/Signup/Account.php' => 'Fixed issue with signup activity not showing up for members in the same networks when feed content is set My Friends & Networks',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Fixed issues with one-way friendships',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.2-4.1.3.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'View/Helper/UserFriendship.php' => 'Fixed issues with one-way friendships',
    'views/scripts/friends/follow.tpl' => 'Added',
    'views/scripts/friends/request-follow.tpl' => 'Fixed issues with one-way friendships',
    'views/scripts/settings/network.tpl' => 'Improvements to network settings page',
    'widgets/list-online/Controller.php' => 'Fixed issue with showing a member before paying for a subscription',
    'widgets/list-popular/Controller.php' => 'Fixed issue with showing a member before paying for a subscription',
    'widgets/list-signups/Controller.php' => 'Fixed issue with showing a member before paying for a subscription',
    'widgets/profile-friends-common/Controller.php' => 'Fixed issues with one-way friendships',
    'widgets/profile-friends-followers/Controller.php' => 'Added',
    'widgets/profile-friends-followers/index.tpl' => 'Added',
    'widgets/profile-friends-following/Controller.php' => 'Added',
    'widgets/profile-friends-following/index.tpl' => 'Added',
    'widgets/profile-friends/Controller.php' => 'Fixed issues with one-way friendships',
    'widgets/profile-friends/index.tpl' => 'Fixed issues with one-way friendships',
  ),
  '4.1.2' => array(
    '/application/languages/en/user.csv' => 'Added phrases',
    'controllers/AdminManageController.php' => 'Fixed issue with deleted networks, editing a member now closes smoothbox on confirmation; Now sends approval email when the approve selected button is used',
    'controllers/AuthController.php' => 'Added ability to base64 encode return URL',
    'controllers/EditController.php' => 'Added ability to remove profile photo',
    'controllers/FriendsController.php' => 'Fixed issue with friend suggestion',
    'controllers/SettingsController.php' => 'Hides profile posting privacy option on form if there are less than one option',
    'externals/styles/main.css' => 'Added styles',
    'Form/Admin/Account.php' => 'Added ability to disable profile address',
    'Form/Admin/Manage/Edit.php' => 'Added ability to disable profile address',
    'Form/Admin/Settings/Level.php' => 'Added ability to disable profile address',
    'Form/Admin/Signup/Account.php' => 'Added ability to disable profile address',
    'Form/Edit/Photo.php' => 'Added ability to remove profile photo',
    'Form/Edit/RemovePhoto.php' => 'Added',
    'Form/Settings/General.php' => 'Added ability to disable profile address',
    'Form/Settings/Privacy.php' => 'Fixed issue with saving privacy options when the options are hidden',
    'Form/Signup/Account.php' => 'Added ability to disable profile address',
    'Plugin/Menus.php' => 'Fixed incorrect profile URL; added return URL to mini menu login link',
    'Plugin/Signup/Account.php' => 'Added save notice to admin form',
    'Plugin/Signup/Invite.php' => 'Added save notice to admin form',
    'Plugin/Signup/Photo.php' => 'Now works on distributed hosting',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added preliminary layout editor enhancements',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/_formSignupImage.tpl' => 'Now works on distributed hosting',
    'views/scripts/admin-signup/index.tpl' => 'Fixed javascript error',
    'views/scripts/edit/remove-photo.tpl' => 'Added',
    'views/scripts/settings/privacy.tpl' => 'Fixes link to unblock members',
    'views/scripts/signup/form/account.tpl' => 'Fixed javascript error',
    'widgets/list-online/Controller.php' => 'Added guest count option',
    'widgets/list-online/index.tpl' => 'Added guest count option',
    'widgets/list-popular/Controller.php' => 'Added configuration options',
  ),
  '4.1.1' => array(
    'controllers/AdminManageController.php' => 'Added network management to edit user',
    'controllers/AuthController.php' => 'Added http_host to lost password link',
    'controllers/SettingsController.php' => 'Removes collate command from passwordAction since it is done at database level now',
    'controllers/SignupController.php' => 'Added welcome email after verification',
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'externals/styles/main.css' => 'CSS tweak for displaying long field names on profile info tab',
    'Form/Admin/Facebook.php' => 'Fixed language',
    'Form/Admin/Manage/Edit.php' => 'Added network management to edit user',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.0-4.1.1.sql' => 'Added',
    'settings/my.sql' => 'Incremented version; changes password and salt columns to UTF-8 for collation compatibility',
  ),
  '4.1.0' => array(
    '/application/languages/en/user.csv' => 'Replacing friend list description for admin; fixed phrases with stray double-quotes',
    'Api/Core.php' => 'Fixed issue with numeric usernames',
    'Bootstrap.php' => 'Disabled users are logged out immediately',
    'controllers/AdminManageController.php' => 'Added new edit user options; users are email when they are enabled',
    'controllers/AdminSettingsController.php' => 'Added notice on form save',
    'controllers/AdminSignupController.php' => 'Account step is no longer required to be first to allow for subscription step to be placed first; fixed bugs with step selection',
    'controllers/AuthController.php' => 'Added code to handle subscriptions; tweak to allow for CLI support; added pre/post login hooks; fixed reset password email link',
    'controllers/EditController.php' => 'Optimization for profile photos',
    'controllers/FriendsController.php' => 'Fixed incorect notification parameters',
    'controllers/IndexController.php' => 'Silencing notices in the content system; unverified users no longer appear in browse members; fixed issue with user name search',
    'controllers/ProfileController.php' => 'Silencing notices in the content system; admins do not require verification',
    'controllers/SettingsController.php' => 'Added logging to Facebook exceptions; removed signup feed item type from settings form',
    'controllers/SignupController.php' => 'Fixed undefined variables for $confirmSession',
    'Form/Admin/Manage/Edit.php' => 'Added option to reset a member password',
    'Form/Admin/Manage/Filter.php' => 'Added filtering options; fixed issue with parameter propagation',
    'Form/Admin/Signup/Account.php' => 'Fixed issues with signup step selection in admin edit page',
    'Form/Admin/Signup/Fields.php' => 'Fixed issues with signup step selection in admin edit page',
    'Form/Admin/Signup/Invite.php' => 'Fixed issues with signup step selection in admin edit page',
    'Form/Admin/Signup/Photo.php' => 'Fixed issues with signup step selection in admin edit page',
    'Form/Settings/General.php' => 'Fixed incorrect regex that allowed numeric usernames',
    'Form/Signup/Account.php' => 'Fixed incorrect regex that allowed numeric usernames',
    'Form/Signup/Fields.php' => 'Added form title',
    'Form/Signup/Invite.php' => 'Fixed incorrect submit button label',
    'Form/Signup/Photo.php' => 'Added form title',
    'Model/DbTable/Online.php' => 'Different',
    'Model/User.php' => 'Tweak to allow for CLI support; added isAdmin() method; fixed issue with resetting password after v3 migration',
    'Plugin/Job/Maintenance/RebuildPrivacy.php' => 'Added',
    'Plugin/Menus.php' => 'Fixed active class for home menu item',
    'Plugin/Signup/Account.php' => 'Added support for the account step to not be the first step',
    'Plugin/Signup/Fields.php' => 'Added support for the account step to not be the first step',
    'Plugin/Signup/Invite.php' => 'Added support for the account step to not be the first step',
    'Plugin/Signup/Photo.php' => 'Added support for the account step to not be the first step',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Removed',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added pagination/item count limits to widgets',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.0rc1-4.0.0rc2.sql' => 'Backwards compatibility fix for tasks modifications',
    'settings/my-upgrade-4.0.3p1-4.0.4.sql' => 'Backwards compatibility fix for tasks modifications',
    'settings/my-upgrade-4.0.4-4.0.5.sql' => 'Backwards compatibility fix for tasks modifications',
    'settings/my-upgrade-4.0.5-4.1.0.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/_browseUsers.tpl' => 'Fixed pagination issues on browse members',
    'views/scripts/admin-manage/index.tpl' => 'Fixed pagination issues; added link to member stats page',
    'views/scripts/admin-manage/stats.tpl' => 'Added',
    'views/scripts/admin-signup/index.tpl' => 'Fixed issues with signup step selection in admin edit page',
    'views/scripts/edit/photo.tpl' => 'Fixed notices caused by invalid variable comparison',
    'views/scripts/edit/style.tpl' => 'Fixed notices caused by invalid variable comparison',
    'views/scripts/index/search.tpl' => 'Code formatting',
    'views/scripts/settings/network.tpl' => 'Fixed autosuggest issue',
    'views/scripts/settings/privacy.tpl' => 'Fixed issue where blocked users that had been deleted could cause exceptions',
    'widgets/list-online/Controller.php' => 'Added pagination/item count limit',
    'widgets/list-popular/Controller.php' => 'Added pagination/item count limit',
    'widgets/list-signups/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-friends-common/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-friends/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-friends/index.tpl' => 'Added pagination/item count limit',
    'widgets/profile-info/Controller.php' => 'Fixed networks member belongs to',
    'widgets/profile-tags/Controller.php' => 'Added pagination/item count limit',
    'widgets/profile-tags/index.tpl' => 'Added pagination/item count limit',
  ),
  '4.0.5' => array(
    'Api/Core.php' => 'Auth now stored user id instead of email to prevent issues with changing email addresses',
    'Bootstrap.php' => 'Removed code that could cause javascript errors',
    'controllers/AuthController.php' => 'Fixed security issue with forgot password',
    'controllers/BlockController.php' => 'Removed deprecated context switch code',
    'controllers/FriendsController.php' => 'Removed deprecated context switch code',
    'controllers/IndexController.php' => 'Name search now searches through username; added dependent field support to browse members',
    'controllers/SettingsController.php' => 'Disabled action and notification types no longer show; added network join activity feed item; fixed issue with disabling friendship feed item',
    'controllers/SignupController.php' => 'Resend verification now creates a new code if missing',
    'Form/Block/Add.php' => 'Fixed issue with context switch',
    'Form/Block/Remove.php' => 'Fixed issue with context switch',
    'Form/Friends/Add.php' => 'Fixed issue with context switch',
    'Form/Friends/Cancel.php' => 'Fixed issue with context switch',
    'Form/Friends/Confirm.php' => 'Fixed issue with context switch',
    'Form/Friends/Reject.php' => 'Fixed issue with context switch',
    'Form/Friends/Remove.php' => 'Fixed issue with context switch',
    'Form/Search.php' => 'Added dependent field support to browse members',
    'Form/Settings/Privacy.php' => 'Code formatting',
    'Form/Signup/Fields.php' => 'Added support for disabling fields on signup',
    'Form/Signup/Photo.php' => 'Added missing .jpeg extension to allowed extension list',
    'Model/DbTable/Facebook.php' => 'Logging tweak',
    'Model/DbTable/Forgot.php' => 'Changed garbage collect to 24 hours',
    'Model/User.php' => 'Added missing translation',
    'Plugin/Task/Cleanup.php' => 'Added idle support',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Added idle support',
    'settings/changelog.php' => 'Added',
    'settings/content.php' => 'Added ability to configure the number of items displayed for certain widgets',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3p1-4.0.4.sql' => 'Fixed missing category for cleanup task',
    'settings/my-upgrade-4.0.4-4.0.5.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/edit/profile.tpl' => 'Fixed errors caused by object comparison method',
    'views/scripts/index/browse.tpl' => 'Added dependent field support',
    'views/scripts/signup/resend.tpl' => 'Added better error messages',
    'widgets/home-links/Controller.php' => 'Fixed issues with caching regardless of language/locale',
    'widgets/home-photo/Controller.php' => 'Fixed issues with caching regardless of language/locale',
    'widgets/list-online/Controller.php' => 'Count no longer includes disabled/unverified members; fixed issues with caching regardless of language/locale; added ability to configure the number of items displayed for certain widgets',
    'widgets/list-online/index.tpl' => 'Count no longer includes disabled/unverified members',
    'widgets/list-popular/Controller.php' => 'Fixed issues with caching regardless of language/locale; added ability to configure the number of items displayed for certain widgets',
    'widgets/list-popular/index.tpl' => 'Fixed issues with caching regardless of language/locale; added ability to configure the number of items displayed for certain widgets',
    'widgets/list-signups/Controller.php' => 'Fixed issues with caching regardless of language/locale; added ability to configure the number of items displayed for certain widgets',
    'widgets/list-signups/index.tpl' => 'Fixed issues with caching regardless of language/locale; added ability to configure the number of items displayed for certain widgets',
    'widgets/profile-friends/index.tpl' => 'Removed deprecated code',
    '/application/languages/en/user.csv' => 'Added missing phrases',
  ),
  '4.0.4' => array(
    'controllers/AuthController.php' => 'Added params to lost password email',
    'controllers/SettingsController.php' => 'Fixed checking of permissions for showing delete account tab',
    'controllers/SignupController.php' => 'Added params to resend verification email; improved confirmation redirection handling; improved verification error reporting',
    'externals/styles/main.css' => 'Improved RTL support',
    'Form/Search.php' => 'Fixed javascript error',
    'Form/Settings/Privacy.php' => 'Fixed comment option type; adjusted network privacy label',
    'Form/Settings/Delete.php' => 'Removed deprecated code',
    'Form/Signup/Account.php' => 'Code formatting; fixed bug in invite code checking',
    'Form/Signup/Invite.php' => 'Code formatting; added email validation; removed deprecated code',
    'Form/Signup/Photo.php' => 'Removed deprecated code',
    'Model/User.php' => 'Fixed non-optimal level check; now cleans out fields values on delete; fixed issue with resetting password after migration',
    'Plugin/Menus.php' => 'Fixed checking of permissions for showing delete account tab',
    'Plugin/Signup/Account.php' => 'Added params to signup emails; fixed bug in invite code checking',
    'Plugin/Signup/Invite.php' => 'Code formatting; uses common method',
    'Plugin/Task/Cleanup.php' => 'Added docblock',
    'Plugin/Task/Maintenance/RebuildPrivacy.php' => 'Added to fix migration problems',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3p1-4.0.4.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/_formButtonCancel.tpl' => 'Removing deprecated code',
    'views/scripts/auth/reset.tpl' => 'Added missing translation',
    'views/scripts/index/browse.tpl' => 'Fixed broken smoothbox binding after searching',
    'views/scripts/signup/confirm.tpl' => 'Fixed incorrect link',
    'views/scripts/signup/index.tpl' => 'Fixed warning message',
    'views/scripts/signup/verify.tpl' => 'Added error messages',
    'widgets/list-online/Controller.php' => 'Fixed locale problems caused by incorrect cache key; now properly excludes members that have opted out',
    'widgets/list-popular/Controller.php' => 'Fixed locale problems caused by incorrect cache key',
    'widgets/list-signups/Controller.php' => 'Fixed locale problems caused by incorrect cache key',
    '/application/languages/en/user.csv' => 'Added phrases',
  ),
  '4.0.3' => array(
    'Api/Core.php' => 'Fixes for empty viewers',
    'controllers/IndexController.php' => 'Disabled members do not show up in browse members page',
    'controllers/ProfileController.php' => 'Admins and moderators can now see disabled member profiles',
    'controllers/SettingsController.php' => 'Proper handling of delete auth; moved notifications to another tab',
    'Form/Settings/General.php' => 'Moved notifications to another tab; added missing translation',
    'Form/Settings/Privacy.php' => 'Respects subject instead of viewer',
    'Model/User.php' => 'Better handling of empty display names',
    'Model/DbTable/Facebook.php' => 'Fixed typo in javascript',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.2-4.0.3.sql' => 'Added',
    'settings/my.sql' => 'Incremented version; moved notifications to another tab',
    'views/scripts/admin-manage/index.tpl' => 'Style tweak',
    'views/scripts/settings/notifications.tpl' => 'Moved notifications to another tab',
    'views/scripts/signup/index.tpl' => 'Fixed missing translation',
    'widgets/list-popular/Controller.php' => 'No longer displays disabled or unverified users',
    'widgets/list-signups/Controller.php' => 'No longer displays disabled or unverified users',
    'widgets/profile-info/index.tpl' => 'Removed link around member type; added missing translation',
    '/application/languages/en/user.csv' => 'Added phrases',
  ),
  '4.0.2' => array(
    'controllers/AdminManageController.php' => 'Added log in as user action',
    'controllers/AuthController.php' => 'Added authentication against SE3 table (if migration tool used)',
    'controllers/EditController.php' => 'Fix for unselected menu',
    'controllers/FriendsController.php' => 'Missing translations',
    'controllers/SettingsController.php' => 'Fixed missing check for invisible networks; fix for unselected menu',
    'externals/scripts/composer_facebook.js' => 'IE compatibility fix',
    'Form/Edit/Photo.php' => 'Removed arbitrary limit on profile photo size',
    'Form/Settings/Privacy.php' => 'Cast element options to array',
    'Form/Signup/Account.php' => 'Fixed a problem with validating the invite code if more than one invite sent to the same email',
    'Model/DbTable/Facebook.php' => 'Facebook ex-user bug fix',
    'Model/User.php' => 'Fixed incorrect photo being used when setting a photo as your profile photo',
    'Plugin/Signup/Photo.php' => 'Fixed notice',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.1-4.0.2.sql' => 'Added',
    'settings/my.sql' => 'Various level settings fixes and enhancements',
    'views/scripts/_formButtonSkipInvite.tpl' => 'Missing translation',
    'views/scripts/_formButtonSkipPhoto.tpl' => 'Missing translation',
    'views/scripts/admin-manage/index.tpl' => 'Added log in as user action',
    'widgets/profile-friends/index.tpl' => 'Fix for orphaned rows in membership table',
    'widgets/profile-info/Controller.php' => 'Fixed missing check for invisible networks',
    'widgets/profile-info/index.tpl' => 'Fixed missing check for invisible networks',
  ),
  '4.0.1' => array(
    'Api/Core.php' => 'Adjustments for trial',
    'controllers/AdminManageController.php' => 'Delete now run in transaction',
    'controllers/AdminSettingsController.php' => 'Fixed problem in level select',
    'controllers/AuthController.php' => 'Facebook fixes; adjustments for trial; faster sending of verification email',
    'controllers/EditController.php' => 'Better cleanup of temporary files and bug fix for making a profile picture',
    'controllers/IndexController.php' => 'Better exception throwing in Facebook module',
    'controllers/SettingsController.php' => 'Fixes forced logout bug when changing email address',
    'controllers/SignupController.php' => 'Fixed bug in resend of verification email',
    'externals/styles/main.css' => 'Style fixes',
    'Form/Login.php' => 'Facebook login bug fixes',
    'Model/User.php' => 'Better cleanup of temporary files',
    'Model/DbTable/Facebook.php' => 'Facebook login refresh bug fix; Facebook wall post fix',
    'Plugin/Signup/Photo.php' => 'Better cleanup of temporary files',
    'settings/manifest.php' => 'Incremented version',
    'widgets/profile-tags/Controller.php' => 'Fixed typos',
  ),
) ?>