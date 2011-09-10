<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: 2Checkout.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Gateway_2Checkout extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->setTitle('Payment Gateway: 2Checkout');

    $description = $this->getTranslator()->translate('PAYMENT_FORM_ADMIN_GATEWAY_2CHECKOUT_DESCRIPTION');
    $description = vsprintf($description, array(
      'https://www.2checkout.com/va/acct/list_usernames',
      'https://www.2checkout.com/va/notifications/',
      'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'payment',
          'controller' => 'ipn',
          'action' => '2Checkout'
        ), 'default', true),
      'https://www.2checkout.com/va/acct/detail_company_info',
      'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'payment',
          'controller' => 'subscription',
          'action' => 'return'
        ), 'default', true) . '?state=return',
      'https://www.2checkout.com/2co/signup',
    ));
    $this->setDescription($description);

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);


    // Elements
    $this->addElement('Text', 'username', array(
      'label' => 'API Username',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));

    $this->addElement('Text', 'password', array(
      'label' => 'API Password',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
  }
}