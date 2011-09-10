<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Api.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Controller_Action_Helper_Api extends Zend_Controller_Action_Helper_Abstract
{
  /**
   * Simply retuns the instance of Engine_Api
   * 
   * @return Engine_Api
   */
  public function direct()
  {
    return Engine_Api::_();
  }
}