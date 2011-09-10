<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Currency.php 7314 2010-09-08 00:19:06Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Form_Admin_Field_Currency extends Fields_Form_Admin_Field
{
  protected $_priorityCurrencies = array(
    'USD' => 1,
    'EUR' => 2,
  );

  protected $_currencies;
  
  public function init()
  {
    parent::init();

    // Add currencies
    $locale = Zend_Registry::get('Zend_Translate')->getLocale();
    $this->_currencies = $currencies = Zend_Locale::getTranslationList('NameToCurrency', $locale);
    uksort($currencies, array($this, '_orderCurrencies'));
    $this->addElement('Select', 'unit', array(
      'label' => 'Currency Type',
      'multiOptions' => $currencies,
      'value' => 'USD',
    ));
  }

  protected function _orderCurrencies($a, $b)
  {
    $ai = @$this->_priorityCurrencies[$a];
    $bi = @$this->_priorityCurrencies[$b];
    if( null !== $ai && null !== $bi ) {
      return ($ai < $bi) ? -1 : 1;
    } else if( null !== $ai ) {
      return -1;
    } else if( null !== $bi ) {
      return 1;
    } else {
      return strcmp($this->_currencies[$a], $this->_currencies[$b]);
    }
  }
}