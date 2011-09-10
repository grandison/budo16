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
class Payment_Form_Admin_Transaction_Filter extends Engine_Form
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

    // Element: gateway_id
    $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $multiOptions = array('' => '');
    foreach( $gatewaysTable->fetchAll() as $gateway ) {
      $multiOptions[$gateway->gateway_id] = $gateway->title;
    }
    $this->addElement('Select', 'gateway_id', array(
      'label' => 'Gateway',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: type
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $multiOptions = (array) $transactionsTable->select()
      ->from($transactionsTable->info('name'), 'type')
      ->distinct(true)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
    if (!empty($multiOptions)) {
      $multiOptions = array_combine(
        array_values($multiOptions),
        array_map('ucfirst', array_values($multiOptions))
      );
      // array_combine() will return false if the array is empty
      if (false === $multiOptions) {
        $multiOptions = array();
      }
    }
    $multiOptions = array_merge(array('' => ''), $multiOptions);
    $this->addElement('Select', 'type', array(
      'label' => 'Type',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: state
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $multiOptions = (array) $transactionsTable->select()
      ->from($transactionsTable->info('name'), 'state')
      ->distinct(true)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
    if (!empty($multiOptions)) {
      $multiOptions = array_combine(
        array_values($multiOptions),
        array_map('ucfirst', array_values($multiOptions))
      );
      // array_combine() will return false if the array is empty
      if (false === $multiOptions) {
        $multiOptions = array();
      }
    }
    $multiOptions = array_merge(array('' => ''), $multiOptions);
    $this->addElement('Select', 'state', array(
      'label' => 'State',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: amount
    // @todo

    // Element: order
    $this->addElement('Hidden', 'order', array(
      'order' => 10004,
    ));
    /*
    $this->addElement('Select', 'order', array(
      'label' => 'Order',
      'multiOptions' => array(
        '' => '',
        'transaction_id' => 'ID',
        'user_id' => 'Member ID',
        'gateway_id' => 'Gateway ID',
        'type' => 'Type',
        'state' => 'State',
        'amount' => 'Amount',
        'timestamp' => 'Date',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
     * 
     */

    // Element: direction
    $this->addElement('Hidden', 'direction', array(
      'order' => 10005,
    ));
    /*
    $this->addElement('Select', 'direction', array(
      'label' => 'Direction',
      'multiOptions' => array(
        '' => '',
        'ASC' => 'A-Z',
        'DESC' => 'Z-A',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
     * 
     */

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