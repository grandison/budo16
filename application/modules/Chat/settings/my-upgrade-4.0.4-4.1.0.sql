-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"chat","controller":"settings"}' WHERE `name` = 'core_admin_main_plugins_chat' LIMIT 1;

-- Enables menu items
UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE `name` = 'core_main_chat' OR `name` = 'core_sitemap_chat';

-- Deletes settings
DELETE FROM `engine4_core_settings` WHERE `name` = 'chat.chat.enabled' OR `name` = 'chat.im.enabled';
