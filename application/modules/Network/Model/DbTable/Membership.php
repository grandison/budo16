<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Membership.php 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_Model_DbTable_Membership extends Core_Model_DbTable_Membership
{
  protected $_type = 'network';

  public function isUserApprovalRequired()
  {
    return false;
  }

  public function isResourceApprovalRequired()
  {
    return false;
  }

  protected function _delete(){

  }
}
