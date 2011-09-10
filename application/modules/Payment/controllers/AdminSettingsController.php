<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSettingsController.php 8199 2011-01-12 00:59:07Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Settings_Global();

    // Populate currency options
    $supportedCurrencyIndex = array();
    $fullySupportedCurrencies = array();
    $supportedCurrencies = array();
    $gateways = array();
    $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    foreach( $gatewaysTable->fetchAll(/*array('enabled = ?' => 1)*/) as $gateway ) {
      $gateways[$gateway->gateway_id] = $gateway->title;
      $gatewayObject = $gateway->getGateway();
      $currencies = $gatewayObject->getSupportedCurrencies();
      if( empty($currencies) ) {
        continue;
      }
      $supportedCurrencyIndex[$gateway->title] = $currencies;
      if( empty($fullySupportedCurrencies) ) {
        $fullySupportedCurrencies = $currencies;
      } else {
        $fullySupportedCurrencies = array_intersect($fullySupportedCurrencies, $currencies);
      }
      $supportedCurrencies = array_merge($supportedCurrencies, $currencies);
    }
    $supportedCurrencies = array_diff($supportedCurrencies, $fullySupportedCurrencies);
    
    $translationList = Zend_Locale::getTranslationList('nametocurrency', Zend_Registry::get('Locale'));
    $fullySupportedCurrencies = array_intersect_key($translationList, array_flip($fullySupportedCurrencies));
    $supportedCurrencies = array_intersect_key($translationList, array_flip($supportedCurrencies));
    $form->getElement('currency')->setMultiOptions(array(
      'Fully Supported' => $fullySupportedCurrencies,
      'Partially Supported' => $supportedCurrencies,
    ));
    
    $this->view->gateways = $gateways;
    $this->view->supportedCurrencyIndex = $supportedCurrencyIndex;

    // Populate form
    $form->populate((array) Engine_Api::_()->getApi('settings', 'core')->payment);

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Save settings
    Engine_Api::_()->getApi('settings', 'core')->payment = $form->getValues();

    
    $form->addNotice('Your changes have been saved.');
  }
}