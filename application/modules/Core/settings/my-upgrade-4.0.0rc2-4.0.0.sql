/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my-upgrade-4.0.0rc2-4.0.0.sql 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
ALTER TABLE  `engine4_core_menuitems` CHANGE  `name`  `name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;

INSERT IGNORE INTO  `engine4_core_menuitems` (
  `name` ,
  `module` ,
  `label` ,
  `plugin` ,
  `params` ,
  `menu` ,
  `submenu` ,
  `order`
) VALUES (
  'core_admin_main_settings_password',  'core',  'Admin Password',  '',  '{"route":"core_admin_settings","action":"password"}',  'core_admin_main_settings',  '',  9
);
