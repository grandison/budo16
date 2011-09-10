<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Order.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Model_Order extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;

  protected $_user;

  protected $_gateway;

  protected $_source;

  /**
   * Get the user attached to this order
   * 
   * @return User_Model_User
   */
  public function getUser()
  {
    if( empty($this->user_id) ) {
      return null;
    }
    if( null === $this->_user ) {
      $this->_user = Engine_Api::_()->getItem('user', $this->user_id);
    }
    return $this->_user;
  }

  /**
   * Get the gateway attached to this order
   * 
   * @return Payment_Model_Gateway
   */
  public function getGateway()
  {
    if( empty($this->gateway_id) ) {
      return null;
    }
    if( null === $this->_gateway ) {
      $this->_gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
    }
    return $this->_gateway;
  }

  /**
   * Get the source object for this order (subscription, cart, etc)
   *
   * @return Core_Model_Item_Abstract
   */
  public function getSource()
  {
    if( empty($this->source_type) || empty($this->source_id) ) {
      return null;
    }
    if( null == $this->_source ) {
      $this->_source = Engine_Api::_()->getItem($this->source_type, $this->source_id);
    }
    return $this->_source;
  }



  // Events

  public function onCancel()
  {
    if( $this->state == 'pending' ) {
      $this->state = 'cancelled';
    }
    $this->save();
    return $this;
  }

  public function onFailure()
  {
    if( $this->state == 'pending' ) {
      $this->state = 'failed';
    }
    $this->save();
    return $this;
  }

  public function onIncomplete()
  {
    if( $this->state == 'pending' ) {
      $this->state = 'incomplete';
    }
    $this->save();
    return $this;
  }

  public function onComplete()
  {
    if( $this->state == 'pending' ) {
      $this->state = 'complete';
    }
    $this->save();
    return $this;
  }
}