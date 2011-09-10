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
class Core_Form_Admin_Language_Upload extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Language Pack')
      ->setDescription('Upload a modified language pack.  This will overwrite any language entries already in a language file.')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    
    
    $languageList     = Zend_Locale_Data::getList('en', 'language');
    $territoryList    = Zend_Locale_Data::getList('en', 'territory');

    $languageNameList = array();
    foreach (array_keys(Zend_Locale::getLocaleList()) as $localeCode) {
      $localeArray = explode('_', $localeCode);
      $locale      = array_shift($localeArray);
      $territory   = array_shift($localeArray);
      if (isset($languageList[$locale]) && !empty($languageList[$locale])) {
        $languageNameList[$localeCode] = $languageList[$locale];
        if (isset($territoryList[$territory]) && !empty($territoryList[$territory])) {
          $languageNameList[$localeCode] .= " ({$territoryList[$territory]})";
        }
        $languageNameList[$localeCode]   .= "  [$localeCode]";
      }
    }
    asort($languageNameList);
    $this->addElement('Select', 'locale', array(
      'label' => 'Language',
      'description' => 'Which language will this language pack be applied to?',
      'multiOptions' => $languageNameList,
    ));

    


    $this->addElement('File', 'file', array(
      'label' => 'Language File',
      'description' => 'Upload a language CSV file.',
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