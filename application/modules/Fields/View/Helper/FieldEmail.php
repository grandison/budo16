<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldEmail.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldEmail extends Fields_View_Helper_FieldAbstract
{
  public function fieldEmail($subject, $field, $value)
  {
    return $this->view->htmlLink('mailto:' . $value->value, $value->value);
  }
}