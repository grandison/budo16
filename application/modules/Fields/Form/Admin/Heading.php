<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Heading.php 8272 2011-01-19 04:21:03Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Form_Admin_Heading extends Engine_Form
{
  public function init()
  {
    $this->setMethod('POST')
      ->setAttrib('class', 'global_form_smoothbox');

    // Add label
    $this->addElement('Text', 'label', array(
      'label' => 'Heading Name',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Display
    $this->addElement('Select', 'display', array(
      'label' => 'Show on Member Profiles?',
      'multiOptions' => array(
        1 => 'Show on Member Profiles',
        0 => 'Hide on Member Profiles'
      )
    ));

    // Show
    $this->addElement('Select', 'show', array(
      'label' => 'Show on Signup/Creation?',
      'multiOptions' => array(
        1 => 'Show on signup/creation',
        0 => 'Hide on signup/creation',
      )
    ));

    // Add submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Add Heading',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Add cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'onclick' => 'parent.Smoothbox.close();',
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}