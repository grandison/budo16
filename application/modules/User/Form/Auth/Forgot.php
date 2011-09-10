<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Forgot.php 7358 2010-09-13 06:53:47Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Auth_Forgot extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Lost Password')
      ->setDescription('USER_FORM_AUTH_FORGOT_DESCRIPTION')
      ->setAttrib('id', 'user_form_auth_forgot')
      ;

    // init email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        'EmailAddress'
      ),
      'tabindex' => 1,
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Email',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => 2,
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => Zend_Registry::get('Zend_Translate')->_(' or '),
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'default', true),
      'decorators' => array(
        'ViewHelper',
      ),
    ));
    
    $this->addDisplayGroup(array(
      'submit',
      'cancel'
    ), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      ),
    ));
  }
}