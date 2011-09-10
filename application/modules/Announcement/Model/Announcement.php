<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Announcement.php 7645 2010-10-15 03:48:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Announcement_Model_Announcement extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';

  protected $_owner_type = 'user';

  public function getHref($params = array())
  {
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble(array(), 'default', true);
  }

  protected function _update()
  {
    parent::_update();
  }
}