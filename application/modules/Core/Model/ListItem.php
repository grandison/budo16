<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ListItem.php 7418 2010-09-20 00:18:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_ListItem extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;
  
  public function getChild()
  {
    $type = $this->getParent()->child_type;
    return Engine_Api::_()->getItem($type, $this->child_id);
  }
}