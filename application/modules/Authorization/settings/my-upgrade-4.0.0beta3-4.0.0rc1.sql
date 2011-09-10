/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: my-upgrade-4.0.0beta3-4.0.0rc1.sql 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
ALTER TABLE  `engine4_authorization_levels`
  CHANGE  `flag`  `flag` ENUM(  'default',  'superadmin',  'public' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
UPDATE  `engine4_authorization_levels` SET  `flag` =  'public' WHERE  `engine4_authorization_levels`.`level_id` = 5;
