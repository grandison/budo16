<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Subscription.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Signup_Subscription extends Engine_Form
{
  protected $_isSignup = true;
  
  public function setIsSignup($flag)
  {
    $this->_isSignup = (bool) $flag;
  }
  
  public function init()
  {
    $this
      ->setTitle('Subscription Plan')
      ->setDescription('Please select a subscription plan from the list below.')
      ;

    // Get available subscriptions
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $packagesSelect = $packagesTable
      ->select()
      ->from($packagesTable)
      ->where('enabled = ?', true)
      ;

    if( $this->_isSignup ) {
      $packagesSelect->where('signup = ?', true);
    }

    $multiOptions = array();
    foreach( $packagesTable->fetchAll($packagesSelect) as $package ) {
      $multiOptions[$package->package_id] = $package->title
        . ' (' . $package->getPackageDescription() . ')'
        ;
    }
    
    // Element: package_id
    //if( count($multiOptions) > 1 ) {
      $this->addElement('Radio', 'package_id', array(
        'label' => 'Choose Plan:',
        'required' => true,
        'allowEmpty' => false,
        'multiOptions' => $multiOptions,
      ));
    //}

    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}