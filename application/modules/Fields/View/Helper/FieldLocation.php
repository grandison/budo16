<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldLocation.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldLocation extends Fields_View_Helper_FieldAbstract
{
  public function fieldLocation($subject, $field, $value)
  {
    return $value->value
      . ' ['
      . $this->view->htmlLink('http://maps.google.com/?q=' . urlencode($value->value), $this->view->translate('map'), array('target' => '_blank'))
      . ']';
  }
}