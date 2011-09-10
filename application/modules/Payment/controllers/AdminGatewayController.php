<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminGatewayController.php 8115 2010-12-23 02:19:59Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_AdminGatewayController extends Core_Controller_Action_Admin
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
    else if( !($info['features'] & CURL_VERSION_SSL) ||
        !in_array('https', $info['protocols']) ) {
      $this->view->error = $this->view->translate('The installed version of ' .
          'the cURL PHP extension does not support HTTPS, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }

    // Make paginator
    $select = Engine_Api::_()->getDbtable('gateways', 'payment')->fetchAll();
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function editAction()
  {
    // Get gateway
    $gateway = Engine_Api::_()->getDbtable('gateways', 'payment')
      ->find($this->_getParam('gateway_id'))
      ->current();

    // Make form
    $this->view->form = $form = $gateway->getPlugin()->getAdminGatewayForm();
    
    // Populate form
    $form->populate($gateway->toArray());
    if( is_array($gateway->config) ) {
      $form->populate($gateway->config);
    }

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();
    
    $enabled = (bool) $values['enabled'];
    //$testMode = !empty($values['test_mode']);
    unset($values['enabled']);
    //unset($values['test_mode']);

    // Validate gateway config
    if( $enabled ) {
      $gatewayObject = $gateway->getGateway();

      try {
        $gatewayObject->setConfig($values);
        $response = $gatewayObject->test();
      } catch( Exception $e ) {
        $enabled = false;
        $form->populate(array('enabled' => false));
        $form->addError(sprintf('Gateway login failed. Please double check ' .
            'your connection information. The gateway has been disabled. ' .
            'The message was: [%2$d] %1$s', $e->getMessage(), $e->getCode()));
      }
    } else {
      $form->addError('Gateway is currently disabled.');
    }

    // Process
    $message = null;
    try {
      $values = $gateway->getPlugin()->processAdminGatewayForm($values);
    } catch( Exception $e ) {
      $message = $e->getMessage();
      $values = null;
    }

    if( $values ) {
      $gateway->setFromArray(array(
        'enabled' => $enabled,
        'config' => $values,
      ));
      $gateway->save();
      
      $form->addNotice('Changes saved.');
    } else {
      $form->addError($message);
    }

    // Try to update/create all product if enabled
    $gatewayPlugin = $gateway->getGateway();
    if( $gateway->enabled &&
        method_exists($gatewayPlugin, 'createProduct') &&
        method_exists($gatewayPlugin, 'editProduct') &&
        method_exists($gatewayPlugin, 'detailVendorProduct') ) {
      $packageTable = Engine_Api::_()->getDbtable('packages', 'payment');
      try {
        foreach( $packageTable->fetchAll() as $package ) {
          if( $package->isFree() ) {
            continue;
          }
          // Check billing cycle support
          if( !$package->isOneTime() ) {
            $sbc = $gateway->getGateway()->getSupportedBillingCycles();
            if( !in_array($package->recurrence_type, array_map('strtolower', $sbc)) ) {
              continue;
            }
          }
          // If it throws an exception, or returns empty, assume it doesn't exist?
          try {
            $info = $gatewayPlugin->detailVendorProduct($package->getGatewayIdentity());
          } catch( Exception $e ) {
            $info = false;
          }
          // Create
          if( !$info ) {
            $gatewayPlugin->createProduct($package->getGatewayParams());
          }
        }
        $form->addNotice('All plans have been checked successfully for products in this gateway.');
      } catch( Exception $e ) {
        $form->addError('We were not able to ensure all packages have a product in this gateway.');
        $form->addError($e->getMessage());
      }
    }
  }

  public function deleteAction()
  {
    $this->view->form = $form = new Payment_Form_Admin_Gateway_Delete();
  }
}