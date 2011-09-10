<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IpnController.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_IpnController extends Core_Controller_Action_Standard
{
  public function __call($method, array $arguments)
  {
    $params = $this->_getAllParams();
    $gatewayType = $params['action'];
    $gatewayId = ( !empty($params['gateway_id']) ? $params['gateway_id'] : null );
    unset($params['module']);
    unset($params['controller']);
    unset($params['action']);
    unset($params['rewrite']);
    unset($params['gateway_id']);
    if( !empty($gatewayType) && 'index' !== $gatewayType ) {
      $params['gatewayType'] = $gatewayType;
    } else {
      $gatewayType = null;
    }

    // Log ipn
    $ipnLogFile = APPLICATION_PATH . '/temporary/log/payment-ipn.log';
    file_put_contents($ipnLogFile,
        date('c') . ': ' .
        print_r($params, true),
        FILE_APPEND);

    // Get gateways
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));

    // Try to detect gateway
    $activeGateway = null;
    foreach( $gateways as $gateway ) {
      $gatewayPlugin = $gateway->getPlugin();

      // Action matches end of plugin
      if( $gatewayType &&
          substr(strtolower($gateway->plugin), - strlen($gatewayType)) == strtolower($gatewayType) ) {
        $activeGateway = $gateway;
      } else if( $gatewayId && $gatewayId == $gateway->gateway_id ) {
        $activeGateway = $gateway;
      } else if( method_exists($gatewayPlugin, 'detectIpn') &&
          $gatewayPlugin->detectIpn($params) ) {
        $activeGateway = $gateway;
      }
    }

    // Gateway could not be detected
    if( !$activeGateway ) {
      echo 'ERR';
      exit();
    }

    // Validate ipn
    $gateway = $activeGateway;
    $gatewayPlugin = $gateway->getPlugin();

    try {
      $ipn = $gatewayPlugin->createIpn($params);
    } catch( Exception $e ) {
      // IPN validation failed
      if( 'development' == APPLICATION_ENV ) {
        echo $e;
      }
      echo 'ERR';
      exit();
    }

    
    // Process IPN
    try {
      $gatewayPlugin->onIpn($ipn);
    } catch( Exception $e ) {
      $gatewayPlugin->getGateway()->getLog()->log($e, Zend_Log::ERR);
      // IPN validation failed
      if( 'development' == APPLICATION_ENV ) {
        echo $e;
      }
      echo 'ERR';
      exit();
    }

    // Exit
    echo 'OK';
    exit();
  }
}