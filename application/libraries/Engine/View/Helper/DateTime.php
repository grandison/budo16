<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: DateTime.php 7301 2010-09-06 23:13:40Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_DateTime extends Zend_View_Helper_FormElement
{
  public function dateTime($datetime)
  {
    trigger_error('Please use the locale view helper: $view->locale()->toDateTime()', E_USER_DEPRECATED);
    return $this->view->locale()->toDateTime($datetime);
  }
}