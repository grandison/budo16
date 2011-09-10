<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Actors.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Model_Helper_Actors extends Activity_Model_Helper_Abstract
{
  public function direct($subject, $object, $separator = ' &rarr; ')
  {
    $pageSubject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

    $subject = $this->_getItem($subject, false);
    $object = $this->_getItem($object, false);
    
    // Check to make sure we have an item
    if( !($subject instanceof Core_Model_Item_Abstract) || !($object instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }

    $attribs = array('class' => 'feed_item_username');

    if( null === $pageSubject ) {
      return $subject->toString($attribs) . $separator . $object->toString($attribs);
    } else if( $pageSubject->isSelf($subject) ) {
      return $subject->toString($attribs) . $separator . $object->toString($attribs);
    } else if( $pageSubject->isSelf($object) ) {
      return $subject->toString($attribs);
    } else {
      return $subject->toString($attribs) . $separator . $object->toString($attribs);
    }
  }
}
