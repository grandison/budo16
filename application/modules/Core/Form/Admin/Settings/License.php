<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: License.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Settings_License extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Update License Key')
      ;
    $this->setAttrib('class', 'global_form_popup');


    // Key
    $this->addElement('Text', 'key', array(
      'label' => 'New License Key:',
      'required' => true,
      'allowEmpty' => true,
      'value' => Engine_Api::_()->getApi('settings', 'core')->core_license_key,
      'validators' => array(
        array('NotEmpty', false),
        new Engine_Validate_Callback(array(get_class($this), 'validateKey'))
      )
    ));
    $this->getElement('key')->getValidator('NotEmpty')
      ->setMessage('Please fill in the license key.', 'notEmptyInvalid')
      ->setMessage('Please fill in the license key.', 'isEmpty');
    $this->getElement('key')->getValidator('Callback')
      ->setMessage('Please enter a valid license key.', 'invalid');


    $this->addElement('Button', 'submit', array(
      'label' => 'Save',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onClick'=> 'javascript:parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');

  }
  
  
  static public function validateKey($value)
  {
    return true;
  }
}
