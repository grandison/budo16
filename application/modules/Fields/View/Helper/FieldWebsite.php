<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FieldWebsite.php 8549 2011-03-03 00:52:44Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_FieldWebsite extends Fields_View_Helper_FieldAbstract
{
  public function fieldWebsite($subject, $field, $value, $params = array())
  {
    $str = $value->value;
    if( strpos($str, 'http://') === false ) {
      $str = 'http://' . $str;
    }

    if (!isset($params['target'])) {
      $params['target'] = '_blank';
    }

    return $this->view->htmlLink($str, $str, $params);
  }
}