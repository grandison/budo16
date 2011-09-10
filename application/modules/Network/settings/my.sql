
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my.sql 8820 2011-04-08 00:31:16Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_network_networks`
--

DROP TABLE IF EXISTS `engine4_network_networks`;
CREATE TABLE `engine4_network_networks` (
  `network_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `field_id` int(11) unsigned NOT NULL default '0',
  `pattern` text NULL,
  `member_count` int(11) unsigned NOT NULL default '0',
  `hide` tinyint(1) NOT NULL default '0',
  `assignment` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`network_id`),
  KEY `assignment` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_network_networks`
--

INSERT IGNORE INTO `engine4_network_networks` (`title`, `field_id`, `pattern`, `assignment`) VALUES
('North America', 0, NULL, 0),
('South America', 0, NULL, 0),
('Europe', 0, NULL, 0),
('Asia', 0, NULL, 0),
('Africa', 0, NULL, 0),
('Australia', 0, NULL, 0),
('Antarctica', 0, NULL, 0)
;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_network_membership`
--

DROP TABLE IF EXISTS `engine4_network_membership`;
CREATE TABLE `engine4_network_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `resource_approved` tinyint(1) NOT NULL default '0',
  `user_approved` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`resource_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Network Membership', 'network_maintenance_rebuild_membership', 'network', 'Network_Plugin_Job_Maintenance_RebuildMembership', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_network', 'network', 'Networks', '', '{"route":"user_extended", "module":"user", "controller":"settings", "action":"network"}', 'user_settings', '', 3)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('network', 'Networks', 'Networks', '4.1.4', 1, 'standard');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('network_join', 'network', '{item:$subject} joined the network {item:$object}', 1, 3, 1, 1, 1, 1)
;
