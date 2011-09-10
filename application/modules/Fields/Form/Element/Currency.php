<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Currency.php 7316 2010-09-08 02:48:34Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Form_Element_Currency extends Engine_Form_Element_Float
{
  protected $_fieldMeta;

  public function setFieldMeta($field)
  {
    $this->_fieldMeta = $field;
    return $this;
  }

  public function init()
  {
    parent::init();

    $this->addFilter('Callback', array(array($this, 'filterRound')));
  }

  public function render(Zend_View_Interface $view = null)
  {
    if( $this->_fieldMeta instanceof Fields_Model_Meta && !empty($this->_fieldMeta->config['unit']) ) {
      //$currency = new Zend_Currency($this->_fieldMeta->config['unit']);
      $localeObject = Zend_Registry::get('Locale');
      $currencyCode = $this->_fieldMeta->config['unit'];
      $currencyName = Zend_Locale_Data::getContent($localeObject, 'nametocurrency', $currencyCode);
      
      $this->loadDefaultDecorators();
      $this->getDecorator('Label')
        ->setOption('optionalSuffix', ' - ' . $currencyCode)
        ->setOption('requiredSuffix', ' - ' . $currencyCode)
        ;

      if( $currencyName && !$this->getDescription() ) {
        $this->setDescription($currencyName);
        $this->getDecorator('Description')->setOption('placement', 'APPEND');
      }
    }
    
    return parent::render($view);
  }

  public function filterRound($value)
  {
    if( empty($value) ) {
      return '0';
    }
    return round($value, 2);
  }
}