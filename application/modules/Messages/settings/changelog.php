<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8943 2011-05-16 21:52:41Z john $
 * @author     John
 */
return array(
  '4.1.5' => array(
    'controllers/MessagesController.php' => 'Fixed issue with page not redirecting properly when deleting messages; Fixed issue with members being able to send messages to blocked user',
    'Plugin/Menus.php' => 'Fixes menu issue with pending friend requests',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/view.tpl' => 'Fixed issue for Opera 11; Prevent members from sending messages to members they blocked',
  ),
  '4.1.4' => array(
    'controllers/MessagesController.php' => 'Fixed issue with input being hidden with invalid username; added delete link to message view',
    'externals/images/delete.png' => 'Added',
    'externals/styles/main.css' => 'Added styles for delete link',
    'externals/styles/mobile.css' => 'Added',
    'Form/Compose.php' => 'Improved error message when to recipient specified',
    'Plugin/Menus.php' => 'Fixed issues with showing send message option when messaging is only allowed between friends',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/compose.tpl' => 'Fixed overtext issues',
    'views/scripts/messages/view.tpl' => 'Added delete link',
  ),
  '4.1.3' => array(
    'externals/styles/main.css' => 'Added styles for messages widget',
    'Plugin/Menus.php' => 'Fixed permission issues with sending messages to friends only, fixed menu-item showing when Messages disabled.',
    'settings/changelog.php' => 'Incremented version',
    'settings/content.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/compose.tpl' => 'Fixed issues member name not being escaped properly',
    'views/scripts/messages/inbox.tpl' => 'Fixed issues with undefine variable notices',
    'views/scripts/messages/outbox.tpl' => 'Fixed issues with undefine variable notices',
    'widgets/home-messages/Controller.php' => 'Added',
    'widgets/home-messages/index.tpl' => 'Added',
  ),
  '4.1.2' => array(
    '/application/languages/en/messages.csv' => 'Added phrases',
    'controllers/MessagesController.php' => 'Fixed issues with messaging all group/event members',
    'Model/Conversation.php' => 'Fixed issues with messaging all group/event members',
    'Model/DbTable/Conversations.php' => 'Fixed issues with messaging all group/event members',
    'Plugin/Menus.php' => 'Fixed issues with messaging all group/event members',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/compose.tpl' => 'Fixed issues with messaging all group/event members',
    'views/scripts/messages/inbox.tpl' => 'Fixed issues with messaging all group/event members',
    'views/scripts/messages/outbox.tpl' => 'Fixed issues with messaging all group/event members',
    'views/scripts/messages/view.tpl' => 'Fixed issues with messaging all group/event members',
  ),
  '4.1.1' => array(
    'externals/.htaccess' => 'Added keywords; removed deprecated code',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.0' => array(
    '/application/languages/en/messages.csv' => 'Added phrases',
    'controllers/MessagesController.php' => 'Removed hard limit on messaging multiple users; fixed UI issue with smoothboxes',
    'externals/styles/main.css' => 'Added RTL styles',
    'Model/Conversation.php' => 'Removed nl2br',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/compose.tpl' => 'Fixed issue preventing usage by mobile devices, such as the iPad and iPhone',
    'views/scripts/messages/delete.tpl' => 'Fixed UI issue with smoothboxes',
    'views/scripts/messages/view.tpl' => 'Fixed issue preventing usage by mobile devices, such as the iPad and iPhone',
  ),
  '4.0.5' => array(
    'controllers/MessagesController.php' => 'Removed deprecated code',
    'Model/Conversation.php' => 'Different',
    'Model/Message.php' => 'Compat for search indexing changes',
    'settings/changelog.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    '/application/languages/en/messages.csv' => 'Added missing phrases',
  ),
  '4.0.4' => array(
    'controllers/MessagesController.php' => 'Removed deprecated code',
    'externals/styles/main.css' => 'Improved RTL support',
    'Model/DbTable/Conversations.php' => 'Added title and user identity',
    'Model/Conversation.php' => 'Removed title from replies for now (it\'s not being used for replies and the auto "Re:" was not getting translated)',
    'views/scripts/messages/compose.tpl' => 'Improved RTL support',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.3-4.0.4.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/messages/inbox.tpl' => 'Added missing translation; fixed conversation title',
    'views/scripts/messages/outbox.tpl' => 'Added missing translation; fixed conversation title',
    'views/scripts/messages/view.tpl' => 'Added missing translation; fixed conversation title',
    '/application/languages/en/messages.csv' => 'Added phrases',
  ),
  '4.0.3' => array(
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.2-4.0.3.sql' => 'Added',
    'settings/my.sql' => 'Incremented version; added email notification template for new message',
    '/application/languages/en/messages.csv' => 'Added phrases',
  ),
  '4.0.2' => array(
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.0.1-4.0.2.sql' => 'Added',
    'settings/my.sql' => 'Various level settings fixes and enhancements',
    'views/scripts/messages/inbox.tpl' => 'Delete Selected is now translated',
  ),
  '4.0.1' => array(
    'controllers/AdminSettingsController.php' => 'Fixed problem in level select',
    'controllers/MessagesController.php' => 'Changed json_encode to Zend_Json::encode',
    'settings/manifest.php' => 'Incremented version',
  ),
) ?>