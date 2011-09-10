<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Log.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_System_Log extends Engine_Form
{
  public function init()
  {
    // Form
    $this
      ->setMethod('GET')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('file' => null)))
      ->addAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ));


    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;


    // Element: file
    $this->addElement('Select', 'file', array(
      'multiOptions' => array(
        '' => '',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: length
    $this->addElement('Select', 'length', array(
      'multiOptions' => array(
        '10' => 'Show 10 Lines',
        '50' => 'Show 50 Lines',
        '100' => 'Show 100 Lines',
        '500' => 'Show 500 Lines',
        '1000' => 'Show 1000 Lines',
        '5000' => 'Show 5000 Lines',
        '10000' => 'Show 10000 Lines',
        '50000' => 'Show 50000 Lines',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'type' => 'submit',
      'label' => 'View Log',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: clear
    $this->addElement('Button', 'clear', array(
      'label' => 'Clear Log',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: download
    $this->addElement('Button', 'download', array(
      'label' => 'Download Log',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
  }
}