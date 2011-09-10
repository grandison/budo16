<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Activity_Model_Helper_Abstract
{
  /**
   * Currently set action
   * 
   * @var Activity_Model_Action
   */
  protected $_action;

  /**
   * Set the current action
   * 
   * @param Activity_Model_Action $action
   * @return Activity_Model_Action
   */
  public function setAction(Activity_Model_Action $action)
  {
    $this->_action = $action;
    return $this;
  }

  /**
   * Get the currently set action
   * @return Activity_Model_Action
   */
  public function getAction()
  {
    return $this->_action;
  }

  /**
   * Accessor
   * 
   * @return string
   */
  public function direct()
  {
    return '';
  }

  protected function _getItem($item, $throw = true)
  {
    // Accept string in form <type>_<id>
    if( is_string($item) && strpos($item, '_') !== false )
    {
      $item = explode('_', $item);
    }

    // Accept array in form array(<type>, <id>)
    if( is_array($item) && count($item) === 2 && is_string($item[0]) && is_numeric($item[1]) )
    {
      $item = Engine_Api::_()->getItem($item[0], $item[1]);
    }

    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      if( $throw ) {
        throw new Activity_Model_Exception('Not an item');
      } else {
        return false;
      }
    }

    return $item;
  }
}