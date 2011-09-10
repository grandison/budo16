<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldCurrency.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldCurrency extends Fields_View_Helper_FieldAbstract
{
  public function fieldCurrency($subject, $field, $value)
  {
    $label = $this->view->locale()->toCurrency($value->value, $field->config['unit']);

    return $this->encloseInLink($subject, $field, $value->value, $label, true);
  }
}