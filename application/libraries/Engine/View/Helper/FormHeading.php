<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FormHeading.php 7244 2010-09-01 01:49:53Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_FormHeading extends Zend_View_Helper_FormElement
{
  public function formHeading($name, $value = null, $attribs = null)
  {
    $info = $this->_getInfo($name, $value, $attribs);
    extract($info); // name, value, attribs
    return '<span'
      . $this->_htmlAttribs($attribs)
      . '>'
      . $value
      . '</span>';
  }
}