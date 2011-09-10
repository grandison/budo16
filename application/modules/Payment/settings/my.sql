
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8942 2011-05-16 21:40:10Z john $
 * @author     John Boehr <j@webligo.com>
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_gateways`
--

DROP TABLE IF EXISTS `engine4_payment_gateways`;
CREATE TABLE IF NOT EXISTS `engine4_payment_gateways` (
  `gateway_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `description` text NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `config` mediumblob NULL,
  `test_mode` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gateway_id`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `engine4_payment_gateways`
--

INSERT INTO `engine4_payment_gateways` (`gateway_id`, `title`, `description`, `enabled`, `plugin`, `test_mode`) VALUES
(1, '2Checkout', NULL, 0, 'Payment_Plugin_Gateway_2Checkout', 0),
(2, 'PayPal', NULL, 0, 'Payment_Plugin_Gateway_PayPal', 0);


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_orders`
--

DROP TABLE IF EXISTS `engine4_payment_orders`;
CREATE TABLE IF NOT EXISTS `engine4_payment_orders` (
  `order_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `gateway_id` int(10) unsigned NOT NULL,
  `gateway_order_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `gateway_transaction_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `state` enum('pending','cancelled','failed','incomplete','complete') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'pending',
  `creation_date` datetime NOT NULL,
  `source_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `source_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `gateway_id` (`gateway_id`,`gateway_order_id`),
  KEY `state` (`state`),
  KEY `source_type` (`source_type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_packages`
--

DROP TABLE IF EXISTS `engine4_payment_packages`;
CREATE TABLE IF NOT EXISTS `engine4_payment_packages` (
  `package_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `level_id` int(10) unsigned NOT NULL,
  `downgrade_level_id` int(10) unsigned NOT NULL default '0',
  `price` decimal(16,2) unsigned NOT NULL,
  `recurrence` int(11) unsigned NOT NULL,
  `recurrence_type` enum('day','week','month','year','forever') NOT NULL,
  `duration` int(11) unsigned NOT NULL,
  `duration_type` enum('day','week','month','year','forever') NOT NULL,
  `trial_duration` int(11) unsigned NOT NULL default '0',
  `trial_duration_type` enum('day','week','month','year','forever') default NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '1',
  `signup` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`package_id`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_products`
--

DROP TABLE IF EXISTS `engine4_payment_products`;
CREATE TABLE IF NOT EXISTS `engine4_payment_products` (
  `product_id` int(10) unsigned NOT NULL auto_increment,
  `extension_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `extension_id` int(10) unsigned default NULL,
  `sku` bigint(20) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(16,2) unsigned NOT NULL,
  PRIMARY KEY  (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `extension_type` (`extension_type`,`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_subscriptions`
--

DROP TABLE IF EXISTS `engine4_payment_subscriptions`;
CREATE TABLE IF NOT EXISTS `engine4_payment_subscriptions` (
  `subscription_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `package_id` int(11) unsigned NOT NULL,
  `status` enum('initial','trial','pending','active','cancelled','expired','overdue','refunded') NOT NULL default 'initial',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime default NULL,
  `payment_date` datetime default NULL,
  `expiration_date` datetime default NULL,
  `notes` text NULL,
  `gateway_id` int(10) unsigned default NULL,
  `gateway_profile_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  PRIMARY KEY  (`subscription_id`),
  UNIQUE KEY `gateway_id` (`gateway_id`, `gateway_profile_id`),
  KEY `user_id` (`user_id`),
  KEY `package_id` (`package_id`),
  KEY `status` (`status`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_payment_transactions`
--

DROP TABLE IF EXISTS `engine4_payment_transactions`;
CREATE TABLE IF NOT EXISTS `engine4_payment_transactions` (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `gateway_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `order_id` int(10) unsigned NOT NULL default '0',

  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `state` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `gateway_transaction_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `gateway_parent_transaction_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `gateway_order_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `amount` decimal(16,2) NOT NULL,
  `currency` char(3) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `gateway_id` (`gateway_id`),
  KEY `type` (`type`),
  KEY `state` (`state`),
  KEY `gateway_transaction_id` (`gateway_transaction_id`),
  KEY `gateway_parent_transaction_id` (`gateway_parent_transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_subscription_active', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_cancelled', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_expired', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_overdue', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_pending', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_recurrence', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_refunded', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]')
;

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

/*
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('payment_admin_main_transactions', 'payment', 'Transactions', '', '{"route":"admin_default","module":"payment","controller":"transactions","action":"index"}', 'payment_admin_main', '', 1),
('payment_admin_main_settings', 'payment', 'Settings', '', '{"route":"admin_default","module":"payment","controller":"settings"}', 'payment_admin_main', '', 2),
('payment_admin_main_gateway', 'payment', 'Gateways', '', '{"route":"admin_default","module":"payment","controller":"gateway"}', 'payment_admin_main', '', 3),
('payment_admin_main_package', 'payment', 'Plans', '', '{"route":"admin_default","module":"payment","controller":"package"}', 'payment_admin_main', '', 4),
('payment_admin_main_subscription', 'payment', 'Subscriptions', '', '{"route":"admin_default","module":"payment","controller":"subscription"}', 'payment_admin_main', '', 5)
;
*/

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_payment', 'user', 'Subscription', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"index"}', 'user_settings', '', 4),

('core_admin_main_payment', 'payment', 'Billing', '', '{"uri":"javascript:void(0);this.blur();"}', 'core_admin_main', 'core_admin_main_payment', 7),

('core_admin_main_payment_transactions', 'payment', 'Transactions', '', '{"route":"admin_default","module":"payment","controller":"index","action":"index"}', 'core_admin_main_payment', '', 1),
('core_admin_main_payment_settings', 'payment', 'Settings', '', '{"route":"admin_default","module":"payment","controller":"settings","action":"index"}', 'core_admin_main_payment', '', 2),
('core_admin_main_payment_gateways', 'payment', 'Gateways', '', '{"route":"admin_default","module":"payment","controller":"gateway","action":"index"}', 'core_admin_main_payment', '', 3),
('core_admin_main_payment_packages', 'payment', 'Plans', '', '{"route":"admin_default","module":"payment","controller":"package","action":"index"}', 'core_admin_main_payment', '', 4),
('core_admin_main_payment_subscriptions', 'payment', 'Subscriptions', '', '{"route":"admin_default","module":"payment","controller":"subscription","action":"index"}', 'core_admin_main_payment', '', 5)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('payment', 'Payment', 'Payment', '4.1.5', 1, 'standard');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('payment.benefit', 'all'),
('payment.currency', 'USD'),
('payment.secret', MD5(CONCAT(RAND(), NOW())));
/*
('payment.subscription.enabled', 0),
('payment.lapse', 'reassign'),
('payment.subscription.required', 0)
*/


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_tasks`
--

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Payment Maintenance', 'user', 'Payment_Plugin_Task_Cleanup', 43200);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_user_signup`
--

INSERT INTO `engine4_user_signup` (`class`, `order`, `enable`) VALUES
('Payment_Plugin_Signup_Subscription', 0, 0)
;
