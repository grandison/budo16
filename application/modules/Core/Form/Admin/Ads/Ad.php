<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Ads_Ad extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this->setTitle('Create Advertisement');
    $this->setDescription('Follow this guide to design and create a new advertisement.');
    $this->setAttrib('id', 'form-upload');
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // Title
    $this->addElement('Text', 'name', array(
      'label' => 'Advertisement Name',
      'allowEmpty' => false,
      'required' => true,
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(1, 64)),
      ),
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    $this->addElement('Radio', 'media_type', array(
      'id'=>'mediatype',
      'label' => 'Advertisement Media',
      'onchange' => "updateTextFields(this)",
      'multiOptions' => array("0"=>"Upload Banner Image", "1"=>"Insert Banner HTML"),
      'description' => 'CORE_FORM_ADMIN_ADS_AD_MEDIATYPE_DESCRIPTION'
    ));
//    $this->media->getDecorator('Description')->setOption('placement', 'append');


    // Init file

    $fancyUpload = new Engine_Form_Element_FancyUpload('file');
    $fancyUpload->clearDecorators()
                ->addDecorator('FormFancyUpload')
                ->addDecorator('viewScript', array(
                  'viewScript' => '_FancyUpload.tpl',
                  'placement'  => '',
                  ));
    Engine_Form::addDefaultDecorators($fancyUpload);
    $fancyUpload->setLabel("Upload Banner Image");
    $this->addElement($fancyUpload);
    $this->addElement('Hidden', 'photo_id');

    $this->addDisplayGroup(array('file'), 'upload_image');
    $upload_image_group = $this->getDisplayGroup('upload_image');

    $this->addElement('Textarea', 'html_code', array(
      'label' => 'HTML Code',
    ));
    // Buttons
    $this->addElement('Button', 'preview_html', array(
      'label' => 'Preview',
      'ignore' => true,
      'onclick'=>'javascript:preview();',
      'decorators' => array('ViewHelper')
    ));

    $this->addDisplayGroup(array('html_code', 'preview_html'), 'html_field');
    $html_code_group = $this->getDisplayGroup('html_code');

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}