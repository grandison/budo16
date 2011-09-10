<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldZipCode.php 8284 2011-01-22 01:59:42Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldZipCode extends Fields_View_Helper_FieldAbstract
{
  public function fieldZipCode($subject, $field, $value)
  {
    return $this->encloseInLink($subject, $field, $value->value, $value->value, true);
  }
}