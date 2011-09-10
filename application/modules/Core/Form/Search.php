<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Search.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Search extends Engine_Form
{
  public function init()
  {
    $this
      ->setMethod('get')
      ->setDecorators(array('FormElements', 'Form'))
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    
    $this->addElement('Text', 'query', array(
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Select', 'type', array(
      'multiOptions' => array(
        '' => 'Everything',
      ),
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Search',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array(
        'ViewHelper',
      ),
    ));
  }
}