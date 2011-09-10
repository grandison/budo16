<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Upload.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Themes_Upload extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Theme Manager')
      ->setDescription('Upload a theme pack')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    $this->addElement('Text', 'title', array(
      'label' => 'Theme title',
      'description' => 'What would you like to title this theme? (Leave blank to use the default theme title)',
    ));

    $this->addElement('Textarea', 'description', array(
      'label' => 'Theme Description',
      'description' => 'Please describe the theme you are uploading.',
    ));

    $this->addElement('File', 'theme_file', array(
      'label' => 'Theme file',
      'description' => 'Select the theme file you wish to upload. Note: this must be a file packaged into a TAR file.',
      'required' => true,
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Upload',
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