<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8355 2011-02-01 01:13:34Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Package_Create extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Create Subscription Plan')
      ->setDescription('Please note that payment parameters (Price, ' .
          'Recurrence, Duration, Trial Duration) cannot be edited after ' .
          'creation. If you wish to change these, you will have to create a ' .
          'new plan and disable the current one.')
      ;

    // Element: title
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: description
    $this->addElement('Textarea', 'description', array(
      'label' => 'Description',
      'validators' => array(
        array('StringLength', true, array(0, 250)),
      )
    ));

    // Element: level_id
    $multiOptions = array('' => '');
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if( $level->type == 'public' || $level->type == 'admin' || $level->type == 'moderator' ) {
        continue;
      }
      $multiOptions[$level->getIdentity()] = $level->getTitle();
    }
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      //'required' => true,
      //'allowEmpty' => false,
      'description' => 'The member will be placed into this level upon ' .
          'subscribing to this plan. If left empty, the default level at the ' .
          'time a subscription is chosen will be used.',
      'multiOptions' => $multiOptions,
    ));

    /*
    // Element: downgrade_level_id
    $this->addElement('Select', 'downgrade_level_id', array(
      'label' => 'Downgrade Member Level',
      'multiOptions' => $multiOptions,
    ));
    */
    
    // Element: price
    $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency');
    $this->addElement('Text', 'price', array(
      'label' => 'Price',
      'description' => 'The amount to charge the member. This will be charged ' .
          'once for one-time plans, and each billing cycle for recurring ' .
          'plans. Setting this to zero will make this a free plan.',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('Float', true),
        new Engine_Validate_AtLeast(0),
      ),
      'value' => '0.00',
    ));

    // Element: recurrence
    $this->addElement('Duration', 'recurrence', array(
      'label' => 'Billing Cycle',
      'description' => 'How often should members in this plan be billed?',
      'required' => true,
      'allowEmpty' => false,
      //'validators' => array(
        //array('Int', true),
        //array('GreaterThan', true, array(0)),
      //),
      'value' => array(1, 'month'),
    ));
    //unset($this->getElement('recurrence')->options['day']);
    //$this->getElement('recurrence')->options['forever'] = 'One-time';
    
    // Element: duration
    $this->addElement('Duration', 'duration', array(
      'label' => 'Billing Duration',
      'description' => 'When should this plan expire? For one-time ' .
        'plans, the plan will expire after the period of time set here. For ' .
        'recurring plans, the user will be billed at the above billing cycle ' .
        'for the period of time specified here.',
      'required' => true,
      'allowEmpty' => false,
      //'validators' => array(
      //  array('Int', true),
      //  array('GreaterThan', true, array(0)),
      //),
      'value' => array('0', 'forever'),
    ));
    //unset($this->getElement('duration')->options['day']);
    
    // Element: trial_duration
    /*
    $this->addElement('Duration', 'trial_duration', array(
      'label' => 'Trial Duration',
      'description' => 'NOT YET IMPLEMENTED. Please note that the way ' .
          'payment gateways implement this varies. PayPal implements this ' .
          'exactly, however 2Checkout uses a negative startup fee. For ' .
          '2Checkout, you must use a multiple of your billing ' .
          'cycle.',
      'validators' => array(
        array('Int', true),
        new Engine_Validate_AtLeast(0),
      ),
      'value' => array('0', 'forever'),
    ));
     * 
     */
    
    // Element: enabled
    $this->addElement('Radio', 'enabled', array(
      'label' => 'Enabled?',
      'description' => 'Can members choose this plan? Please note that disabling this plan will grandfather in existing plan members until they pick a new plan.',
      'multiOptions' => array(
        '1' => 'Yes, members may select this plan.',
        '0' => 'No, members may not select this plan.',
      ),
      'value' => 1,
    ));

    // Element: signup
    $this->addElement('Radio', 'signup', array(
      'label' => 'Show on signup?',
      'description' => 'Can members choose this plan on signup?',
      'multiOptions' => array(
        '1' => 'Yes, show this plan on signup.',
        '0' => 'No, only show this plan after signup.',
      ),
      'value' => 1,
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Create Plan',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index', 'package_id' => null)),
      'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      )
    ));
  }
}