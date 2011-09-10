<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldFacebook.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldFacebook extends Fields_View_Helper_FieldAbstract
{
  public function fieldFacebook($subject, $field, $value)
  {
    $facebookUrl = stripos($value->value, 'facebook.com/') === false
                 ? 'http://www.facebook.com/search/?q=' . $value->value
                 : $value->value;
    return $this->view->htmlLink($facebookUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
  }
}