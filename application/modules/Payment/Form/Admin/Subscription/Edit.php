<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Subscription_Edit extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Edit Subscription')
      ;

    $this->setAttrib('class', 'global_form_popup');

    $this->addElement('Select', 'status', array(
      'label' => 'Status',
      'description' => 'Note: this is provided for adjustment. Changing this ' .
          'will not have any effect on existing transactions. For example, ' .
          'changing this to "cancelled" will not refund any transactions or ' .
          'cancel any recurring payment profiles, however if it was ' .
          'previously "active," the member will have to create a new ' .
          'subscription. Please use the details link on ' .
          'Manage Subscriptions page to perform these actions.',
      'multiOptions' => array(
        'initial' => 'Initializing',
        'trial' => 'Trial',
        'pending' => 'Payment Pending',
        'active' => 'Active',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        'overdue' => 'Overdue',
        'refunded' => 'Refunded',
      ),
    ));

    $this->addElement('Select', 'active', array(
      'label' => 'Active',
      'description' => 'Is this the current, most relevant subscription for ' .
          'this member? Non-active subscriptions have no effect and ' .
          'are stored for record-keeping purposes.',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}