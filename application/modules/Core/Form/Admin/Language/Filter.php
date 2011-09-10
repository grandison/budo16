<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Filter.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Language_Filter extends Engine_Form
{
  public function init()
  {
    $this
      ->setMethod('GET')
      ->setAttrib('class', 'global_form_box')
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Init search
    $this->addElement('Text', 'search', array(
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Init show
    $this->addElement('Select', 'show', array(
      'multiOptions' => array(
        'all' => 'All',
        'missing' => 'Missing',
        'translated' => 'Translated',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Search',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
  }
}