<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminIndexController.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_AdminIndexController extends Core_Controller_Action_Admin
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
    $this->view->formFilter = $formFilter = new Payment_Form_Admin_Transaction_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array();
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'transaction_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    // Initialize select
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $transactionSelect = $transactionsTable->select()
      ;

    // Add filter values
    if( !empty($filterValues['gateway_id']) ) {
      $transactionSelect->where('gateway_id = ?', $filterValues['gateway_id']);
    }
    if( !empty($filterValues['type']) ) {
      $transactionSelect->where('type = ?', $filterValues['type']);
    }
    if( !empty($filterValues['state']) ) {
      $transactionSelect->where('state = ?', $filterValues['state']);
    }
    if( !empty($filterValues['query']) ) {
      $transactionSelect
        ->from($transactionsTable->info('name'))
        ->joinRight('engine4_users', 'engine4_users.user_id=engine4_payment_transactions.user_id', null)
        ->where('(gateway_transaction_id LIKE ? || ' .
            'gateway_parent_transaction_id LIKE ? || ' .
            'gateway_order_id LIKE ? || ' .
            'displayname LIKE ? || username LIKE ? || ' .
            'email LIKE ?)', '%' . $filterValues['query'] . '%');
        ;
    }
    if( ($user_id = $this->_getParam('user_id', @$filterValues['user_id'])) ) {
      $this->view->filterValues['user_id'] = $user_id;
      $transactionSelect->where('engine4_payment_transactions.user_id = ?', $user_id);
    }
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'DESC';
      }
      $transactionSelect->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }






    $this->view->paginator = $paginator = Zend_Paginator::factory($transactionSelect);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Preload info
    $gatewayIds = array();
    $userIds = array();
    $orderIds = array();
    foreach( $paginator as $transaction ) {
      if( !empty($transaction->gateway_id) ) {
        $gatewayIds[] = $transaction->gateway_id;
      }
      if( !empty($transaction->user_id) ) {
        $userIds[] = $transaction->user_id;
      }
      if( !empty($transaction->order_id) ) {
        $orderIds[] = $transaction->order_id;
      }
    }
    $gatewayIds = array_unique($gatewayIds);
    $userIds = array_unique($userIds);
    $orderIds = array_unique($orderIds);

    // Preload gateways
    $gateways = array();
    if( !empty($gatewayIds) ) {
      foreach( Engine_Api::_()->getDbtable('gateways', 'payment')->find($gatewayIds) as $gateway ) {
        $gateways[$gateway->gateway_id] = $gateway;
      }
    }
    $this->view->gateways = $gateways;

    // Preload users
    $users = array();
    if( !empty($userIds) ) {
      foreach( Engine_Api::_()->getItemTable('user')->find($userIds) as $user ) {
        $users[$user->user_id] = $user;
      }
    }
    $this->view->users = $users;

    // Preload orders
    $orders = array();
    if( !empty($orderIds) ) {
      foreach( Engine_Api::_()->getDbtable('orders', 'payment')->find($orderIds) as $order ) {
        $orders[$order->order_id] = $order;
      }
    }
    $this->view->orders = $orders;
  }

  public function detailAction()
  {
    // Missing transaction
    if( !($transaction_id = $this->_getParam('transaction_id')) ||
        !($transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id)) ) {
      return;
    }

    $this->view->transaction = $transaction;
    $this->view->gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);
    $this->view->order = Engine_Api::_()->getItem('payment_order', $transaction->order_id);
    $this->view->user = Engine_Api::_()->getItem('user', $transaction->user_id);
  }

  public function detailTransactionAction()
  {
    $transaction_id = $this->_getParam('transaction_id');
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
    $gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);

    $link = null;
    if( $this->_getParam('show-parent') ) {
      if( !empty($transaction->gateway_parent_transaction_id) ) {
        $link = $gateway->getPlugin()->getTransactionDetailLink($transaction->gateway_parent_transaction_id);
      }
    } else {
      if( !empty($transaction->gateway_transaction_id) ) {
        $link = $gateway->getPlugin()->getTransactionDetailLink($transaction->gateway_transaction_id);
      }
    }

    if( $link ) {
      return $this->_helper->redirector->gotoUrl($link, array('prependBase' => false));
    } else {
      die();
    }
  }

  public function detailOrderAction()
  {
    $transaction_id = $this->_getParam('transaction_id');
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
    $gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);

    if( !empty($transaction->gateway_order_id) ) {
      $link = $gateway->getPlugin()->getOrderDetailLink($transaction->gateway_order_id);
    } else {
      $link = false;
    }

    if( $link ) {
      return $this->_helper->redirector->gotoUrl($link, array('prependBase' => false));
    } else {
      die();
    }
  }

  public function rawOrderDetailAction()
  {
    // By transaction
    if( null != ($transaction_id = $this->_getParam('transaction_id')) &&
        null != ($transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id)) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);
      $gateway_order_id = $transaction->gateway_order_id;
    }

    // By order
    else if( null != ($order_id = $this->_getParam('order_id')) &&
        null != ($order = Engine_Api::_()->getItem('payment_order', $order_id)) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $order->gateway_id);
      $gateway_order_id = $order->gateway_order_id;
    }

    // By raw string
    else if( null != ($gateway_order_id = $this->_getParam('gateway_order_id')) &&
        null != ($gateway_id = $this->_getParam('gateway_id')) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $gateway_id);
    }

    if( !$gateway || !$gateway_order_id  ) {
      $this->view->data = false;
      return;
    }

    $gatewayPlugin = $gateway->getPlugin();

    try {
      $data = $gatewayPlugin->getOrderDetails($gateway_order_id);
      $this->view->data = $this->_flattenArray($data);
    } catch( Exception $e ) {
      $this->view->data = false;
      return;
    }
  }

  public function rawTransactionDetailAction()
  {
    // By transaction
    if( null != ($transaction_id = $this->_getParam('transaction_id')) &&
        null != ($transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id)) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);
      $gateway_transaction_id = $transaction->gateway_transaction_id;
    }

    // By order
    else if( null != ($order_id = $this->_getParam('order_id')) &&
        null != ($order = Engine_Api::_()->getItem('payment_order', $order_id)) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $order->gateway_id);
      $gateway_transaction_id = $order->gateway_transaction_id;
    }

    // By raw string
    else if( null != ($gateway_transaction_id = $this->_getParam('gateway_transaction_id')) &&
        null != ($gateway_id = $this->_getParam('gateway_id')) ) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $gateway_id);
    }

    if( !$gateway || !$gateway_transaction_id  ) {
      $this->view->data = false;
      return;
    }

    $gatewayPlugin = $gateway->getPlugin();

    try {
      $data = $gatewayPlugin->getTransactionDetails($gateway_transaction_id);
      $this->view->data = $this->_flattenArray($data);
    } catch( Exception $e ) {
      $this->view->data = false;
      return;
    }
  }

  protected function _flattenArray($array, $separator = '_', $prefix = '')
  {
    if( !is_array($array) ) {
      return false;
    }

    $flattenedArray = array();
    foreach( $array as $key => $value ) {
      $newPrefix = ( $prefix != '' ? $prefix . $separator : '' ) . $key;
      if( is_array($value) ) {
        $flattenedArray = array_merge($flattenedArray,
            $this->_flattenArray($value, $separator, $newPrefix));
      } else {
        $flattenedArray[$newPrefix] = $value;
      }
    }

    return $flattenedArray;
  }
}