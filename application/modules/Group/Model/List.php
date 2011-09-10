<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: List.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Model_List extends Core_Model_List
{
  protected $_owner_type = 'group';

  protected $_child_type = 'user';

  public $ignorePermCheck = true;

  public function getListItemTable()
  {
    return Engine_Api::_()->getItemTable('group_list_item');
  }
}