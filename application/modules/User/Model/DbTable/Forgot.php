<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Forgot.php 7462 2010-09-24 03:15:46Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Model_DbTable_Forgot extends Engine_Db_Table
{
  public function gc()
  {
    // Delete everything older than <del>6</del> 24 hours
    $this->delete(array(
      'creation_date < ?' => date('Y-m-d H:i:s', time() - (3600 * 24)),
    ));
  }
}