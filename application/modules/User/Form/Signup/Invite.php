<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Invite.php 7664 2010-10-19 22:04:06Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Signup_Invite extends Engine_Form
{
  public $invalid_emails = array();
  public $already_members = array();
  public $emails_sent = 0;

  public function init()
  {
    $this
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('id', 'SignupForm');


    // Init settings object
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $translate = Zend_Registry::get('Zend_Translate');

    // Init form
    $this
      ->setTitle('Invite Your Friends')
      ->setDescription('_INVITE_FORM_DESCRIPTION')
      ->setLegend('');


    // Init recipients
    $this->addElement(new Engine_Form_Element_Textarea('recipients', array(
      'label' => 'Recipients',
      'description' => 'Comma-separated list, or one-email-per-line.',
      'validators' => array(
        new Engine_Validate_Callback(array($this, 'validateEmails')),
      ),
    )));
    $this->recipients->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));


    // Init custom message
    if( $settings->getSetting('invite.allowCustomMessage', 1) > 0 ) {
      $this->addElement('Textarea', 'message', array(
        'label' => 'Custom Message',
        'required' => FALSE,
        'allowEmpty' => TRUE,
        'value' => $settings->getSetting('invite.message'),
        'filters' => array(
          new Engine_Filter_Censor(),
        )
      ));
    }
    $this->message->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    $this->addElement('Hidden', 'nextStep', array(
      'order' => 3
    ));

    $this->addElement('Hidden', 'skip', array(
      'order' => 4
    ));

    // Element: done
    $this->addElement('Button', 'done', array(
      'label' => 'Send Invites',
      'type' => 'submit',
      'onclick' => 'javascript:finishForm();',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Element: skip
    $this->addElement('Cancel', 'skip-link', array(
      'label' => 'skip',
      'prependText' => ' or ',
      'link' => true,
      'href' => 'javascript:void(0);',
      'onclick' => 'skipForm(); return false;',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('done', 'skip-link'), 'buttons', array(

    ));
  }
  
  public function validateEmails($value)
  {
    // Not string?
    if( !is_string($value) || empty($value) ) {
      return false;
    }

    // Validate emails
    $validate = new Zend_Validate_EmailAddress();

    $emails = array_unique(array_filter(array_map('trim', preg_split("/[\s,]+/", $value))));

    if( empty($emails) ) {
      return false;
    }

    foreach( $emails as $email ) {
      if( !$validate->isValid($email) ) {
        return false;
      }
    }

    return true;
  }
}
