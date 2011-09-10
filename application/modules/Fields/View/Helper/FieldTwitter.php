<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldTwitter.php 8131 2010-12-31 03:34:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldTwitter extends Fields_View_Helper_FieldAbstract
{
  public function fieldTwitter($subject, $field, $value)
  {
    return $this->view->htmlLink('http://www.twitter.com/' . $value->value, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
  }
}