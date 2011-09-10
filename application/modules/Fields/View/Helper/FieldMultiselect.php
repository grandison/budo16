<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldMultiselect.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldMultiselect extends Fields_View_Helper_FieldAbstract
{
  public function fieldMultiselect($subject, $field, $value)
  {
    // Build values
    $vals = array();
    foreach( $value as $singleValue ) {
      if( is_string($singleValue) ) {
        $vals[] = $singleValue;
      } else if( is_object($singleValue) ) {
        $vals[] = $singleValue->value;
      }
    }

    $options = $field->getOptions();
    $first = true;
    $content = '';
    foreach( $options as $option ) {
      if( !in_array($option->option_id, $vals) ) continue;
      if( !$first ) $content .= ', ';
      $first = false;

      $label = $this->view->translate($option->label);
      $content .= $this->encloseInLink($subject, $field, $option->option_id, $label);
    }

    return $content;
  }
}
