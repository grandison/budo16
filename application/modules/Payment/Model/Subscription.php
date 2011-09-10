<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Subscription.php 8906 2011-04-21 00:22:33Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Model_Subscription extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;

  protected $_user;

  protected $_gateway;

  protected $_package;

  protected $_statusChanged;

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

  public function getPackage()
  {
    if( empty($this->package_id) ) {
      return null;
    }
    if( null === $this->_package ) {
      $this->_package = Engine_Api::_()->getItem('payment_package', $this->package_id);
    }
    return $this->_package;
  }



  // Actions
  
  public function upgradeUser()
  {
    $user = $this->getUser();
    $level = $this->getPackage()->getLevel();
    if( $user && $level && $user->level_id != $level->level_id ) {
      $user->level_id = $level->level_id;
    }
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    return $this;
  }

  public function downgradeUser()
  {
    $user = $this->getUser();
    //$level = $this->getPackage()->getDowngradeLevel();
    $level = Engine_Api::_()->getDbtable('levels', 'authorization')->getDefaultLevel();
    if( $user && $level && $user->level_id != $level->level_id ) {
      $user->level_id = $level->level_id;
    }
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    return $this;
  }

  public function cancel()
  {
    // Try to cancel recurring payments in the gateway
    if( !empty($this->gateway_id) && !empty($this->gateway_profile_id) ) {
      try {
        $gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
        $gatewayPlugin = $gateway->getPlugin();
        if( method_exists($gatewayPlugin, 'cancelSubscription') ) {
          $gatewayPlugin->cancelSubscription($this->gateway_profile_id);
        }
      } catch( Exception $e ) {
        // Silence?
      }
    }
    // Cancel this row
    $this->active = false; // Need to do this to prevent clearing the user's session
    $this->onCancel();
    return $this;
  }


  // Active

  public function setActive($flag = true, $deactivateOthers = null)
  {
    $this->active = true;

    if( (true === $flag && null === $deactivateOthers) ||
        $deactivateOthers === true ) {
      $table = $this->getTable();
      $select = $table->select()
        ->where('user_id = ?', $this->user_id)
        ->where('active = ?', true)
        ;
      foreach( $table->fetchAll($select) as $otherSubscription ) {
        $otherSubscription->setActive(false);
      }
    }

    $this->save();
    return $this;
  }



  // Events

  public function clearStatusChanged()
  {
    $this->_statusChanged = null;
    return $this;
  }

  public function didStatusChange()
  {
    return (bool) $this->_statusChanged;
  }
  
  public function onPaymentSuccess()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {

      // If the subscription is in initial or pending, set as active and
      // cancel any other active subscriptions
      if( in_array($this->status, array('initial', 'pending')) ) {
        $this->setActive(true);
        Engine_Api::_()->getDbtable('subscriptions', 'payment')
          ->cancelAll($this->getUser(), 'User cancelled the subscription.', $this);
      }
      
      // Update expiration to expiration + recurrence or to now + recurrence?
      $package = $this->getPackage();
      $expiration = $package->getExpirationDate();
      if( $expiration ) {
        $this->expiration_date = date('Y-m-d H:i:s', $expiration);
      }
      
      // Change status
      if( $this->status != 'active' ) {
        $this->status = 'active';
        $this->_statusChanged = true;
      }

      // Update user if active
      if( $this->active ) {
        $this->upgradeUser();
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();

    return $this;
  }

  public function onPaymentPending()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {
      // Change status
      if( $this->status != 'pending' ) {
        $this->status = 'pending';
        $this->_statusChanged = true;
      }

      // Downgrade and log out user if active
      if( $this->active ) {
        // @todo should we do this?
        // Downgrade user
        $this->downgradeUser();

        // Remove active sessions?
        //Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    
    return $this;
  }

  public function onPaymentFailure()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue')) ) {
      // Change status
      if( $this->status != 'overdue' ) {
        $this->status = 'overdue';
        $this->_statusChanged = true;
      }

      // Downgrade and log out user if active
      if( $this->active ) {
        // Downgrade user
        $this->downgradeUser();

        // Remove active sessions?
        Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    
    return $this;
  }

  public function onCancel()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled')) ) {
      // Change status
      if( $this->status != 'cancelled' ) {
        $this->status = 'cancelled';
        $this->_statusChanged = true;
      }

      // Downgrade and log out user if active
      if( $this->active ) {
        // Downgrade user
        $this->downgradeUser();

        // Remove active sessions?
        Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    
    return $this;
  }

  public function onExpiration()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'expired')) ) {
      // Change status
      if( $this->status != 'expired' ) {
        $this->status = 'expired';
        $this->_statusChanged = true;
      }

      // Downgrade and log out user if active
      if( $this->active ) {
        // Downgrade user
        $this->downgradeUser();

        // Remove active sessions?
        Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    
    return $this;
  }

  public function onRefund()
  {
    $this->_statusChanged = false;
    if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'refunded')) ) {
      // Change status
      if( $this->status != 'refunded' ) {
        $this->status = 'refunded';
        $this->_statusChanged = true;
      }

      // Downgrade and log out user if active
      if( $this->active ) {
        // Downgrade user
        $this->downgradeUser();

        // Remove active sessions?
        Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
      }
    }
    $this->save();

    // Check if the member should be enabled
    $user = $this->getUser();
    $user->enabled = true; // This will get set correctly in the update hook
    $user->save();
    
    return $this;
  }
}