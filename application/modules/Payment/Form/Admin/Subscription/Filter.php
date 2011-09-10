<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Filter.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Subscription_Filter extends Engine_Form
{
  public function init()
  {
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET')
      ;

    // Element: query
    $this->addElement('Text', 'query', array(
      'label' => 'Search',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: package_id
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $multiOptions = array('' => '');
    foreach( $packagesTable->fetchAll() as $package ) {
      $multiOptions[$package->package_id] = $package->title;
    }
    $this->addElement('Select', 'package_id', array(
      'label' => 'Plan',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: level_id
    //$this->addElement('Select', 'level_id', array(
    //  'label' => 'Member Level',
    //));

    // Element: status
    $this->addElement('Select', 'status', array(
      'label' => 'Status',
      'multiOptions' => array(
        '' => '',
        'initial' => 'Initial',
        'trial' => 'Trial',
        'pending' => 'Pending Payment',
        'active' => 'Active',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        'overdue' => 'Overdue',
        'refunded' => 'Refunded',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: active
    $this->addElement('Select', 'active', array(
      'label' => 'Active',
      'multiOptions' => array(
        '' => '',
        '1' => 'Yes',
        '0' => 'No',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: order
    $this->addElement('Hidden', 'order', array(
      'order' => 10004,
    ));

    // Element: direction
    $this->addElement('Hidden', 'direction', array(
      'order' => 10005,
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Search',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
        array('HtmlTag2', array('tag' => 'div')),
      ),
    ));
  }
}