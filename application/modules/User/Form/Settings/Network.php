<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Network.php 8558 2011-03-04 01:12:51Z jung $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Settings_Network extends Engine_Form
{
  public function init()
  {    
    $this
      ->setAttrib('id', 'network-form')
      ->setAttrib('method', 'POST')
      ->setAttrib('class', '')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Text', 'title', array(
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Hidden', 'leave_id', array(
      'order' => 990,
    ));
    
    $this->addElement('Hidden', 'join_id', array(
      'order' => 991,
    ));

    //$this->loadDefaultDecorators();
    //$this->removeDecorator('FormContainer');
  }
}