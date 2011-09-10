<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Templates.php 7322 2010-09-09 05:05:22Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Mail_Templates extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this
      ->setTitle('Mail Templates')
      ->setDescription('CORE_FORM_ADMIN_SETTINGS_EMAIL_DESCRIPTION')
      ;

    // Element: language
    $this->addElement('Select', 'language', array(
      'label' => 'Language Pack',
      'description' => 'Your community has more than one language pack installed. Please select the language pack you want to edit right now.',
      'onchange' => 'javascript:setEmailLanguage(this.value);',
    ));

    // Languages
    $localeObject = Zend_Registry::get('Locale');
    $translate    = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach( /*array_keys(Zend_Locale::getLocaleList())*/ $languageList as $key ) {
      $languageName = null;
      if( !empty($languages[$key]) ) {
        $languageName = $languages[$key];
      } else {
        $tmpLocale = new Zend_Locale($key);
        $region = $tmpLocale->getRegion();
        $language = $tmpLocale->getLanguage();
        if( !empty($languages[$language]) && !empty($territories[$region]) ) {
          $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
        }
      }

      if( $languageName ) {
        $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
      }
    }
    
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( isset($localeMultiOptions[$defaultLanguage]) ) {
      $localeMultiOptions = array_merge(array(
        $defaultLanguage => $localeMultiOptions[$defaultLanguage],
      ), $localeMultiOptions);
    }

    $this->language->setMultiOptions($localeMultiOptions);


    // Element: template_id
    $this->addElement('Select', 'template', array(
      'label' => 'Choose Message',
      'onchange' => 'javascript:fetchEmailTemplate(this.value);',
      'ignore' => true
    ));
    $this->template->getDecorator("Description")->setOption("placement", "append");

    foreach( Engine_Api::_()->getDbtable('MailTemplates', 'core')->fetchAll() as $mailTemplate ) {
      $title = $translate->_(strtoupper("_email_".$mailTemplate->type."_title"));
      $this->template->addMultiOption($mailTemplate->mailtemplate_id, $title);
    }

    // Element: subject
    $this->addElement('Text', 'subject', array(
      'label' => 'Subject',
      'style' => 'min-width:400px;',
    ));

    // Element: body
    $this->addElement('Textarea', 'body', array(
      'label' => 'Message Body',
    ));


    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}