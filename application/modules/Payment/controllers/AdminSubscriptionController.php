<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSubscriptionController.php 8186 2011-01-10 23:36:35Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_AdminSubscriptionController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Test curl support
    if( !function_exists('curl_version') ||
        !($info = curl_version()) ) {
      $this->view->error = $this->view->translate('The PHP extension cURL ' .
          'does not appear to be installed, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    // Test curl ssl support
    else if( !($info['features'] & CURL_VERSION_SSL) ||
        !in_array('https', $info['protocols']) ) {
      $this->view->error = $this->view->translate('The installed version of ' .
          'the cURL PHP extension does not support HTTPS, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    // Check for enabled payment gateways
    else if( Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0 ) {
      $this->view->error = $this->view->translate('There are currently no ' .
          'enabled payment gateways. You must %1$sadd one%2$s before this ' .
          'page is available.', '<a href="' .
          $this->view->escape($this->view->url(array('controller' => 'gateway'))) .
          '">', '</a>');
    }


    
    // Make form
    $this->view->formFilter = $formFilter = new Payment_Form_Admin_Subscription_Filter();
    
    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      if( null === $this->_getParam('active') ) {
        $formFilter->populate(array('active' => 1));
      }
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array(
        'active' => 1,
      );
      $formFilter->populate(array('active' => 1));
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'subscription_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    // Initialize select
    $table = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $select = $table->select()
      ;

    // Add filter values
    if( isset($filterValues['active']) && '' != $filterValues['active'] ) {
      $select->where('active = ?', $filterValues['active']);
    }
    if( !empty($filterValues['status']) ) {
      $select->where('status = ?', $filterValues['status']);
    }
    if( !empty($filterValues['package_id']) ) {
      $select->where('package_id = ?', $filterValues['package_id']);
    }
    if( !empty($filterValues['query']) ) {
      $select
        ->from($table->info('name'))
        ->joinRight('engine4_users', 'engine4_users.user_id=engine4_payment_subscriptions.user_id', null)
        ->where('(displayname LIKE ? || username LIKE ? || email LIKE ?)', '%' . $filterValues['query'] . '%');
    }
    if( ($user_id = $this->_getParam('user_id', @$filterValues['user_id'])) ) {
      $this->view->filterValues['user_id'] = $user_id;
      $select->where('engine4_payment_subscriptions.user_id = ?', $user_id);
    }
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'DESC';
      }
      $select->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Get users
    $userIds = array();
    foreach( $paginator as $subscription ) {
      $userIds[] = $subscription->user_id;
    }
    $userIds = array_unique($userIds);

    $users = array();
    if (!empty($userIds)) {
      foreach( Engine_Api::_()->getItemTable('user')->fetchAll(array('user_id IN(?)' => $userIds)) as $user ) {
        $users[$user->user_id] = $user;
      }
    }
    $this->view->users = $users;


    // Get packages
    $packages = array();
    foreach( Engine_Api::_()->getDbtable('packages', 'payment')->fetchAll() as $package ) {
      $packages[$package->package_id] = $package;
    }
    $this->view->packages = $packages;


    // Get levels
    $levels = array();
    foreach( Engine_Api::_()->getItemTable('authorization_level')->fetchAll() as $level ) {
      $levels[$level->level_id] = $level;
    }
    $this->view->levels = $levels;
  }

  public function detailAction()
  {
    if( !($subscription_id = $this->_getParam('subscription_id')) ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscription_id)) ) {
      return;
    }

    $this->view->subscription = $subscription;
    $this->view->user = $user = $subscription->getUser();
    $this->view->package = $package = $subscription->getPackage();
    if( !empty($user->level_id) ) {
      $this->view->actualLevel = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    }
    $this->view->level = $package->getLevel();

    // get any relevant orders
    $ordersRaw = Engine_Api::_()->getDbtable('orders', 'payment')->fetchAll(array(
      'source_type = ?' => 'payment_subscription',
      'source_id = ?' => $subscription->subscription_id,
    ));

    $orders = array();
    $orderIds = array();
    foreach( $ordersRaw as $order ) {
      $orders[$order->order_id] = $order;
      $orderIds[] = $order->order_id;
    }
    $this->view->orders = $orders;

    // get any relevant transactions and orders
    if( !empty($orderIds) ) {
      $this->view->transactions = $transactions = Engine_Api::_()->getDbtable('transactions', 'payment')->fetchAll(array(
        'order_id IN(?)' => $orderIds,
      ));
    }

    // Get gateways
    $gateways = array();
    foreach( Engine_Api::_()->getDbtable('gateways', 'payment')->fetchAll() as $gateway ) {
      $gateways[$gateway->gateway_id] = $gateway;
    }
    $this->view->gateways = $gateways;
  }

  public function editAction()
  {
    if( !($subscription_id = $this->_getParam('subscription_id')) ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscription_id)) ) {
      return;
    }

    $this->view->form = $form = new Payment_Form_Admin_Subscription_Edit();

    $form->populate($subscription->toArray());

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $subscription->setFromArray($form->getValues());
    $subscription->save();

    $this->view->form = null;

    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => array(Zend_Registry::get('Zend_Translate')->_("Changes Saved"))
    ));
  }

  public function cancelAction()
  {
    if( !($subscription_id = $this->_getParam('subscription_id')) ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscription_id)) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'subscription_id' => null));
    }

    $this->view->subscription_id = $subscription_id;

    $this->view->form = $form = new Payment_Form_Admin_Subscription_Cancel();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Try to cancel
    $this->view->form = null;
    try {
      $subscription->cancel();
      $this->view->status = true;
    } catch( Exception $e ) {
      $this->view->status = false;
      $this->view->error = $e->getMessage();
    }
  }
}