<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Externals.php 7244 2010-09-01 01:49:53Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_Externals extends Zend_View_Helper_Abstract
{
  public function externals()
  {
    trigger_error('Deprecated', E_USER_WARNING);
    return $this;
  }
}