<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Clone.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Themes_Clone extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Theme Manager')
      ->setDescription('Clone a theme pack')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    $this->addElement('Text', 'title', array(
      'label' => 'Theme Title',
    ));

    $this->addElement('Textarea', 'description', array(
      'label' => 'Theme Description',
    ));

    $this->addElement('Text', 'author', array(
      'label' => 'Theme Author',
      'value' => Engine_Api::_()->getApi('settings', 'core')->core_general_site_title,
    ));

    $this->addElement('Select', 'name', array(
      'label' => 'Theme to clone',
      'multiOptions' => array(),
    ));

    #$this->addElement('Checkbox', 'enable', array(
    #   'label' => 'Activate immediately',
    #));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Clone',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'prependText' => ' or ',
      'link' => true,
      'label' => 'cancel',
      'onclick' => 'history.go(-1); return false;',
      'decorators' => array(
        'ViewHelper'
      )
    ));
  }
}