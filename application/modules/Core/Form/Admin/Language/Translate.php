<?php

class Core_Form_Admin_Language_Translate extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Translate Language')
      ->setDescription('Translate a language pack using Google Translate.')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Build language list
    $translate        = Zend_Registry::get('Zend_Translate');
    //$translate        = new Zend_Translate_Adapter();
    $languageList     = Zend_Locale_Data::getList('en', 'language');
    $territoryList    = Zend_Locale_Data::getList('en', 'territory');

    //var_dump(array_intersect(Engine_Service_GTranslate::getAvailableLanguages(), array_keys($languageList)));
    //var_dump(array_diff(Engine_Service_GTranslate::getAvailableLanguages(), array_keys($languageList)));
    //var_dump(array_diff(array_keys($languageList), Engine_Service_GTranslate::getAvailableLanguages()));
    //die();

    $languageNameList = array();
    foreach( array_keys(Zend_Locale::getLocaleList()) as $localeCode ) {
      $lang_array = explode('_', $localeCode);
      $locale     = array_shift($lang_array);
      $territory  = array_shift($lang_array);

      // Full locale
      $languageName = null;
      if( isset($languageList[$localeCode]) ) {
        $languageName = $languageList[$locale] . ' [' . $localeCode . ']';
      }
      // Parial locale?
      else if( isset($languageList[$locale]) ) {
        $languageName = $languageList[$locale];
        if( !empty($territoryList[$territory]) ) {
          $languageName .= ' (' . $territoryList[$territory] . ')';
        }
        $languageName .= ' [' . $localeCode . ']';
      }
      // Missing locale
      else {
        //$languageName = '[' . $localeCode . ']';
      }

      // Check against gtranslate
      if( !Engine_Service_GTranslate::isAvailableLanguage($localeCode) ) {
        continue;
      //} else if( !Engine_Service_GTranslate::testAvailableLanguage($localeCode) ) {
      //  echo 'Bad: ' . $localeCode . '<br />' . PHP_EOL;
      //  continue;
      //} else {
      //  echo 'Good: ' . $localeCode . '<br />' . PHP_EOL;
      }

      if( $languageName ) {
        $languageNameList[$localeCode] = $languageName;
      }
    }
    asort($languageNameList);

    // Let's pull the existing languages to the top?
    $existingLanguageNameList = array();
    $notExistingLanguageNameList = array();
    foreach( $translate->getList() as $locale ) {
      if( isset($languageNameList[$locale]) ) {
        $existingLanguageNameList[$locale] = $languageNameList[$locale];
      }
    }

    $notExistingLanguageNameList = array_diff_key($languageNameList, $existingLanguageNameList);

    //$languageNameList = array_merge($existingLanguageNameList, $languageNameList);
    $targetMultiOptions = array_merge($existingLanguageNameList, $notExistingLanguageNameList);
    $targetMultiOptions = array(
      'Translated' => $existingLanguageNameList,
      'Untranslated' => $notExistingLanguageNameList,
      'Special' => array(
        'all' => 'All Available',
        'all-translated' => 'All Translated',
        'all-untranslated' => 'All Untranslated',
      ),
    );



    
    // Element: source
    $this->addElement('Select', 'source', array(
      'label' => 'Source language',
      'value' => 'en',
      'required' => true,
      'allowEmpty' => false,
    ));
    
    foreach( $translate->getList() as $locale ) {
      if( !Engine_Service_GTranslate::isAvailableLanguage($locale) ) {
        continue;
      }
      $this->source->addMultiOption($locale, (@$languageNameList[$locale] ? $languageNameList[$locale] : $locale));
    }

    // Element: target
    $this->addElement('Select', 'target', array(
      'label' => 'Target Language',
      'multiOptions' => array_merge(array('' => ''), $targetMultiOptions),
      'required' => true,
      'allowEmpty' => false,
    ));

    // Element: batchCount
    $this->addElement('Text', 'batchCount', array(
      'label' => 'Batch Count',
      'allowEmpty' => false,
      'validators' => array(
        'Int',
      ),
      'value' => 50,
    ));

    // Element: overwrite
    $this->addElement('Radio', 'overwrite', array(
      'label' => 'Retranslate',
      'description' => 'Do you want to retranslate existing phrases?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => '0',
    ));

    // Element: test
    $this->addElement('Text', 'test', array(
      'label' => 'Test Translation',
      'description' => 'Test Translation',
    ));

    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Translate',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'prependText' => ' or ',
      'link' => true,
      'label' => 'cancel',
      'onclick' => 'history.go(-1); return false;',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}