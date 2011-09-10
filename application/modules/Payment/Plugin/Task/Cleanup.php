<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Cleanup.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Plugin_Task_Cleanup extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');


    // Get subscriptions that have expired or have finished their trial period
    // (trial is not yet implemented)
    $select = $subscriptionsTable->select()
      ->where('expiration_date <= ?', new Zend_Db_Expr('NOW()'))
      ->where('status = ?', 'active')
      //->where('status IN(?)', array('active', 'trial'))
      ->order('subscription_id ASC')
      ->limit(10)
      ;

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $package = $subscription->getPackage();
      // Check if the package has an expiration date
      $expiration = $package->getExpirationDate();
      if( !$expiration ) {
        continue;
      }
      // It's expired
      // @todo send an email
      $subscription->onExpiration();
    }

    
    // Get subscriptions that are old and are pending payment
    $select = $subscriptionsTable->select()
      ->where('status IN(?)', array('initial', 'pending'))
      ->where('expiration_date <= ?', new Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 2 DAY)'))
      ->order('subscription_id ASC')
      ->limit(10)
      ;

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $subscription->onCancel();
    }
  }
}


