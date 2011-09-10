<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Password.php 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Settings_Password extends Engine_Form
{
  public function init()
  {
    // @todo fix form CSS/decorators
    // @todo replace fake values with real values
    $this->setTitle('Change Password')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Init old password
    $this->addElement('Password', 'oldPassword', array(
      'label' => 'Old Password',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Init password
    $this->addElement('Password', 'password', array(
      'label' => 'New Password',
      'description' => 'Passwords must be at least 6 characters in length.',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('stringLength', false, array(6, 32))
      )
    ));
    $this->password->getDecorator('Description')->setOption('placement', 'APPEND');

    // Init password confirm
    $this->addElement('Password', 'passwordConfirm', array(
      'label' => 'New Password (again)',
      'description' => 'Enter your password again for confirmation.',
      'required' => true,
      'allowEmpty' => false
    ));
    $this->passwordConfirm->getDecorator('Description')->setOption('placement', 'APPEND');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Change Password',
      'type' => 'submit',
      'ignore' => true
    ));
    
    // Create display group for buttons
    #$this->addDisplayGroup($emailAlerts, 'checkboxes');

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
  }
}
