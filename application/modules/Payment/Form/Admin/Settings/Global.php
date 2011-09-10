<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Settings_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    // Element: currency
    $this->addElement('Select', 'currency', array(
      'label' => 'Currency',
      'value' => 'USD',
      'description' => '-',
    ));
    $this->getElement('currency')->getDecorator('Description')->setOption('placement', 'APPEND');

    // Element: benefit
    $this->addElement('Radio', 'benefit', array(
      'label' => 'Initial Subscription Status',
      'description' => 'Do you want to enable subscriptions immedately after '
          . 'payment, before the payment passes the gateways\' fraud checks? '
          . 'This may take anywhere from 20 minutes to 4 days, depending on '
          . 'the circumstances and the gateway.',
      'multiOptions' => array(
        'all' => 'Enable subscriptions immediately.',
        'some' => 'Enable if member has an existing successful transaction, wait if this is their first.',
        'none' => 'Wait until the gateway signals that the payment has completed successfully.',
      ),
    ));

    // Element: lapse
    /*
    $this->addElement('Radio', 'lapse', array(
      'label' => 'Subscription Lapse',
      'multiOptions' => array(
        'disable' => 'Disable the account when the subscription lapses.',
        'reassign' => 'Re-assign to the default level.',
      )
    ));
     * 
     */

    /*
    // Element: grace
    $this->addElement('Text', 'grace', array(
      'label' => 'Grace Period',
      'description' => 'How many days grace period is allowed after a failed ' .
          'or missed payment before the account is disabled or re-assigned?',
      'validators' => array(
        array('Int', true),
        new Engine_Validate_AtLeast(0),
      ),
    ));
     * 
     */

    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
    ));
  }
}