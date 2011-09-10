<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Themes.php 7684 2010-10-21 03:49:33Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Themes extends Engine_Db_Table
{

  /**
   * Deletes all temporary files in the Scaffold cache
   *
   * @example self::clearScaffoldCache();
   * @return void
   */
  public static function clearScaffoldCache()
  {
    try {
      Engine_Package_Utilities::fsRmdirRecursive(APPLICATION_PATH . '/temporary/scaffold', false);
    } catch( Exception $e ) {}
  }
}
