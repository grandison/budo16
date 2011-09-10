<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Body.php 7301 2010-09-06 23:13:40Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Model_Helper_Body extends Activity_Model_Helper_Abstract
{
  /**
   * Body helper
   * 
   * @param string $body
   * @return string
   */
  public function direct($body)
  {
    if( Zend_Registry::isRegistered('Zend_View') ) {
      $view = Zend_Registry::get('Zend_View');
      $body = $view->viewMore($body);
    }
    return '<span class="feed_item_bodytext">' . $body . '</span>';
  }
}