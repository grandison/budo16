<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminPackageController.php 8390 2011-02-03 23:35:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_AdminPackageController extends Core_Controller_Action_Admin
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
    $this->view->formFilter = $formFilter = new Payment_Form_Admin_Package_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      if( null === $this->_getParam('enabled') ) {
        $formFilter->populate(array('enabled' => 1));
      }
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array(
        'enabled' => 1,
      );
      $formFilter->populate(array('enabled' => 1));
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'package_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    // Initialize select
    $table = Engine_Api::_()->getDbtable('packages', 'payment');
    $select = $table->select();

    // Add filter values
    if( !empty($filterValues['query']) ) {
      $select->where('title LIKE ?', '%' . $filterValues['package_id'] . '%');
    }
    if( !empty($filterValues['level_id']) ) {
      $select->where('level_id = ?', $filterValues['level_id']);
    }
    if( isset($filterValues['enabled']) && '' != $filterValues['enabled'] ) {
      $select->where('enabled = ?', $filterValues['enabled']);
    }
    if( isset($filterValues['signup']) && '' != $filterValues['signup'] ) {
      $select->where('signup = ?', $filterValues['signup']);
    }
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'ASC';
      }
      $select->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }
    
    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Get member totals for each plan
    $memberCounts = array();
    foreach( $paginator as $item ) {
      $memberCounts[$item->package_id] = Engine_Api::_()->getDbtable('subscriptions', 'payment')
        ->select()
        ->from('engine4_payment_subscriptions', new Zend_Db_Expr('COUNT(*)'))
        ->where('package_id = ?', $item->package_id)
        ->where('active = ?', true)
        ->where('status = ?', 'active')
        ->query()
        ->fetchColumn();
    }
    $this->view->memberCounts = $memberCounts;
  }

  public function createAction()
  {
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Package_Create();

    // Get supported billing cycles
    $gateways = array();
    $supportedBillingCycles = array();
    $partiallySupportedBillingCycles = array();
    $fullySupportedBillingCycles = null;
    $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    foreach( $gatewaysTable->fetchAll(/*array('enabled = ?' => 1)*/) as $gateway ) {
      $gateways[$gateway->gateway_id] = $gateway;
      $supportedBillingCycles[$gateway->gateway_id] = $gateway->getGateway()->getSupportedBillingCycles();
      $partiallySupportedBillingCycles = array_merge($partiallySupportedBillingCycles, $supportedBillingCycles[$gateway->gateway_id]);
      if( null === $fullySupportedBillingCycles ) {
        $fullySupportedBillingCycles = $supportedBillingCycles[$gateway->gateway_id];
      } else {
        $fullySupportedBillingCycles = array_intersect($fullySupportedBillingCycles, $supportedBillingCycles[$gateway->gateway_id]);
      }
    }
    $partiallySupportedBillingCycles = array_diff($partiallySupportedBillingCycles, $fullySupportedBillingCycles);

    $multiOptions = /* array(
      'Fully Supported' =>*/ array_combine(array_map('strtolower', $fullySupportedBillingCycles), $fullySupportedBillingCycles)/*,
      'Partially Supported' => array_combine(array_map('strtolower', $partiallySupportedBillingCycles), $partiallySupportedBillingCycles),
    )*/;
    $form->getElement('recurrence')
      ->setMultiOptions($multiOptions)
      //->setDescription('-')
      ;
    $form->getElement('recurrence')->options/*['Fully Supported']*/['forever'] = 'One-time';

    $form->getElement('duration')
      ->setMultiOptions($multiOptions)
      //->setDescription('-')
      ;
    $form->getElement('duration')->options/*['Fully Supported']*/['forever'] = 'Forever';

    /*
    $form->getElement('trial_duration')
      ->setMultiOptions($multiOptions)
      //->setDescription('-')
      ;
    $form->getElement('trial_duration')->options['Fully Supported']['forever'] = 'None';
    //$form->getElement('trial_duration')->setValue('0 forever');
     * 
     */

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();

    $tmp = $values['recurrence'];
    unset($values['recurrence']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['recurrence'] = (int) $tmp[0];
    $values['recurrence_type'] = $tmp[1];

    $tmp = $values['duration'];
    unset($values['duration']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['duration'] = (int) $tmp[0];
    $values['duration_type'] = $tmp[1];

    /*
    $tmp = $values['trial_duration'];
    unset($values['trial_duration']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['trial_duration'] = (int) $tmp[0];
    $values['trial_duration_type'] = $tmp[1];
     * 
     */
    

    $packageTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $db = $packageTable->getAdapter();
    $db->beginTransaction();

    try {

      // Create package
      $package = $packageTable->createRow();
      $package->setFromArray($values);
      $package->save();

      // Create package in gateways?
      if( !$package->isFree() ) {
        $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
        foreach( $gatewaysTable->fetchAll(array('enabled = ?' => 1)) as $gateway ) {
          $gatewayPlugin = $gateway->getGateway();
          // Check billing cycle support
          if( !$package->isOneTime() ) {
            $sbc = $gateway->getGateway()->getSupportedBillingCycles();
            if( !in_array($package->recurrence_type, array_map('strtolower', $sbc)) ) {
              continue;
            }
          }
          if( method_exists($gatewayPlugin, 'createProduct') ) {
            $gatewayPlugin->createProduct($package->getGatewayParams());
          }
        }
      }

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    // Redirect
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function editAction()
  {
    // Get package
    if( null === ($packageIdentity = $this->_getParam('package_id')) ||
        !($package = Engine_Api::_()->getDbtable('packages', 'payment')->find($packageIdentity)->current()) ) {
      throw new Engine_Exception('No package found');
    }
    
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Package_Edit();

    // Populate form
    $values = $package->toArray();
   
    $values['recurrence'] = array($values['recurrence'], $values['recurrence_type']);
    $values['duration'] = array($values['duration'], $values['duration_type']);
    //$values['trial_duration'] = array($values['trial_duration'], $values['trial_duration_type']);
    
    //unset($values['recurrence']);
    unset($values['recurrence_type']);
    //unset($values['duration']);
    unset($values['duration_type']);
    //unset($values['trial_duration']);
    //unset($values['trial_duration_type']);

    $otherValues = array(
      'price' => $values['price'],
      'recurrence' => $values['recurrence'],
      'duration' => $values['duration'],
    );
    
    $form->populate($values);


    
    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Hack em up
    $form->populate($otherValues);

    // Process
    $values = $form->getValues();

    /*
    $tmp = $values['recurrence'];
    unset($values['recurrence']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['recurrence'] = (int) $tmp[0];
    $values['recurrence_type'] = $tmp[1];

    $tmp = $values['duration'];
    unset($values['duration']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['duration'] = (int) $tmp[0];
    $values['duration_type'] = $tmp[1];

    $tmp = $values['trial_duration'];
    unset($values['trial_duration']);
    if( empty($tmp) || !is_array($tmp) ) {
      $tmp = array(null, null);
    }
    $values['trial_duration'] = (int) $tmp[0];
    $values['trial_duration_type'] = $tmp[1];
    */
    unset($values['price']);
    unset($values['recurrence']);
    unset($values['recurrence_type']);
    unset($values['duration']);
    unset($values['duration_type']);
    unset($values['trial_duration']);
    unset($values['trial_duration_type']);

    $packageTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $db = $packageTable->getAdapter();
    $db->beginTransaction();

    try {
      
      $package->setFromArray($values);
      $package->save();
      
      // Create package in gateways?
      if( !$package->isFree() ) {
        $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
        foreach( $gatewaysTable->fetchAll(array('enabled = ?' => 1)) as $gateway ) {
          $gatewayPlugin = $gateway->getGateway();
          // Check billing cycle support
          if( !$package->isOneTime() ) {
            $sbc = $gateway->getGateway()->getSupportedBillingCycles();
            if( !in_array($package->recurrence_type, array_map('strtolower', $sbc)) ) {
              continue;
            }
          }
          if( !method_exists($gatewayPlugin, 'createProduct') ||
              !method_exists($gatewayPlugin, 'editProduct') ||
              !method_exists($gatewayPlugin, 'detailVendorProduct') ) {
            continue;
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
          // Edit
          else {
            $gatewayPlugin->editProduct($package->getGatewayIdentity(), $package->getGatewayParams());
          }
        }
      }
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $form->addNotice('Your changes have been saved.');
  }

  public function deleteAction()
  {
    return; // Plans can not currently be deleted

    // Get package
    if( null === ($packageIdentity = $this->_getParam('package_id')) ||
        !($package = Engine_Api::_()->getDbtable('packages', 'payment')->find($packageIdentity)->current()) ) {
      throw new Engine_Exception('No package found');
    }


    
    $this->view->form = $form = new Payment_Form_Admin_Package_Delete();



    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }



    // Process

    $packageTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $db = $packageTable->getAdapter();
    $db->beginTransaction();
    
    try {

      // Delete package in gateways?
      $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
      foreach( $gatewaysTable->fetchAll(array('enabled = ?' => 1)) as $gateway ) {
        $gatewayPlugin = $gateway->getGateway();
        if( method_exists($gatewayPlugin, 'deleteProduct') ) {
          try {
            $gatewayPlugin->deleteProduct($package->getGatewayIdentity());
          } catch( Exception $e ) {} // Silence?
        }
      }

      // Delete package
      $package->delete();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
  }
}