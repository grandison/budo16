<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Account.php 8913 2011-05-02 18:03:07Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Signup_Account extends Engine_Form
{

  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $inviteSession = new Zend_Session_Namespace('invite');
    
    // Init form
    $this->setTitle('Create Account');

    // Element: email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'description' => 'You will use your email address to login.',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true),
        array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'email'))
      ),
    ));
    $this->email->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    $this->email->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');

    if( !empty($inviteSession->invite_email) ) {
      $this->email->setValue($inviteSession->invite_email);
    }

    //if( $settings->getSetting('user.signup.verifyemail', 0) > 0 && $settings->getSetting('user.signup.checkemail', 0) == 1 ) {
    //  $this->email->addValidator('Identical', true, array($inviteSession->invite_email));
    //  $this->email->getValidator('Identical')->setMessage('Your email address must match the address that was invited.', 'notSame');
    //}
    
    // Element: code
    if( $settings->getSetting('user.signup.inviteonly') > 0 ) {
      $codeValidator = new Engine_Validate_Callback(array($this, 'checkInviteCode'), $this->email);
      $codeValidator->setMessage("This invite code is invalid or does not match the selected email address");
      $this->addElement('Text', 'code', array(
        'label' => 'Invite Code',
        'required' => true
      ));
      $this->code->addValidator($codeValidator);

      if( !empty($inviteSession->invite_code) ) {
        $this->code->setValue($inviteSession->invite_code);
      }
    }


    if( $settings->getSetting('user.signup.random', 0) == 0 ) {

      // Element: password
      $this->addElement('Password', 'password', array(
        'label' => 'Password',
        'description' => 'Passwords must be at least 6 characters in length.',
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('StringLength', false, array(6, 32)),
        )
      ));
      $this->password->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
      $this->password->getValidator('NotEmpty')->setMessage('Please enter a valid password.', 'isEmpty');

      // Element: passconf
      $this->addElement('Password', 'passconf', array(
        'label' => 'Password Again',
        'description' => 'Enter your password again for confirmation.',
        'required' => true,
        'validators' => array(
          array('NotEmpty', true),
        )
      ));
      $this->passconf->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
      $this->passconf->getValidator('NotEmpty')->setMessage('Please make sure the "password" and "password again" fields match.', 'isEmpty');

      $specialValidator = new Engine_Validate_Callback(array($this, 'checkPasswordConfirm'), $this->password);
      $specialValidator->setMessage('Password did not match', 'invalid');
      $this->passconf->addValidator($specialValidator);
    }

    // Element: username
    if( $settings->getSetting('user.signup.username', 1) > 0 ) {
      $description = Zend_Registry::get('Zend_Translate')
          ->_('This will be the end of your profile link, for example: <br /> ' .
              '<span id="profile_address">http://%s</span>');
      $description = sprintf($description, $_SERVER['HTTP_HOST']
          . Zend_Controller_Front::getInstance()->getRouter()
          ->assemble(array('id' => 'yourname'), 'user_profile'));

      $this->addElement('Text', 'username', array(
        'label' => 'Profile Address',
        'description' => $description,
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('Alnum', true),
          array('StringLength', true, array(4, 64)),
          array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'username'))
        ),
          //'onblur' => 'var el = this; en4.user.checkUsernameTaken(this.value, function(taken){ el.style.marginBottom = taken * 100 + "px" });'
      ));
      $this->username->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid profile address.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this profile address, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Profile addresses must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Profile addresses must be alphanumeric.', 'notAlnum');
    }
    
    // Element: profile_type
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      if( count($options) > 1 ) {
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
              'required' => true,
              'allowEmpty' => false,
            )));
      } else if( count($options) == 1 ) {
        $this->addElement('Hidden', 'profile_type', array(
          'value' => $options[0]->option_id
        ));
      }
    }

    // Element: timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Timezone',
      'value' => $settings->getSetting('core.locale.timezone'),
      'multiOptions' => array(
        'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
        'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
        'US/Central' => '(UTC-6) Central Time (US & Canada)',
        'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
        'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
        'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
        'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
        'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
        'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
        'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
        'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
        'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
        'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
        'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
        'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
        'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
        'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
        'Iran' => '(UTC+3:30) Tehran',
        'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
        'Asia/Kabul' => '(UTC+4:30) Kabul',
        'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
        'Asia/Dili' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
        'Asia/Katmandu' => '(UTC+5:45) Nepal',
        'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
        'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
        'Asia/Bangkok' => '(UTC+7) Bangkok, Jakarta, Hanoi',
        'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
        'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
        'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
        'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
        'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
        'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
      )
    ));
    $this->timezone->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    // Element: language

    // Languages
    $translate = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    //$currentLocale = Zend_Registry::get('Locale')->__toString();
    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( !in_array($defaultLanguage, $languageList) ) {
      if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }

    // Prepare language name list
    $localeObject = Zend_Registry::get('Locale');
    
    $languageNameList = array();
    $languageDataList = Zend_Locale_Data::getList($localeObject, 'language');
    $territoryDataList = Zend_Locale_Data::getList($localeObject, 'territory');

    foreach( $languageList as $localeCode ) {
      $languageNameList[$localeCode] = Zend_Locale::getTranslation($localeCode, 'language', $localeCode);
      if( empty($languageNameList[$localeCode]) ) {
        list($locale, $territory) = explode('_', $localeCode);
        $languageNameList[$localeCode] = "{$territoryDataList[$territory]} {$languageDataList[$locale]}";
      }
    }
    $languageNameList = array_merge(array(
      $defaultLanguage => $defaultLanguage
    ), $languageNameList);

    if(count($languageNameList)>1){
      $this->addElement('Select', 'language', array(
        'label' => 'Language',
        'multiOptions' => $languageNameList
      ));
      $this->language->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    }
    else{
      $this->addElement('Hidden', 'language', array(
        'value' => $languageNameList
      ));
    }

    // Element: captcha
    if( Engine_Api::_()->getApi('settings', 'core')->core_spam_signup ) {
      $this->addElement('captcha', 'captcha', array(
        'description' => '_CAPTCHA_DESCRIPTION',
        'captcha' => 'image',
        'required' => true,
        'allowEmpty' => false,
        'captchaOptions' => array(
          'wordLen' => 6,
          'fontSize' => '30',
          'timeout' => 300,
          'imgDir' => APPLICATION_PATH . '/public/temporary/',
          'imgUrl' => $this->getView()->baseUrl() . '/public/temporary',
          'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf'
        )
      ));
    }
    
    if( $settings->getSetting('user.signup.terms', 1) == 1 ) {
      // Element: terms
      $description = Zend_Registry::get('Zend_Translate')->_('I have read and agree to the <a target="_blank" href="%s/help/terms">terms of service</a>.');
      $description = sprintf($description, Zend_Controller_Front::getInstance()->getBaseUrl());

      $this->addElement('Checkbox', 'terms', array(
        'label' => 'Terms of Service',
        'description' => $description,
        'required' => true,
        'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
        )
      ));
      $this->terms->getValidator('GreaterThan')->setMessage('You must agree to the terms of service to continue.', 'notGreaterThan');
      //$this->terms->getDecorator('Label')->setOption('escape', false);

      $this->terms->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::APPEND, 'tag' => 'label', 'class' => 'null', 'escape' => false, 'for' => 'terms'))
          ->addDecorator('DivDivDivWrapper');

      //$this->terms->setDisableTranslator(true);
    }

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue',
      'type' => 'submit',
      'ignore' => true,
    ));

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));
  }

  public function checkPasswordConfirm($value, $passwordElement)
  {
    return ( $value == $passwordElement->getValue() );
  }

  public function checkInviteCode($value, $emailElement)
  {
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $select = $inviteTable->select()
      ->from($inviteTable->info('name'), 'COUNT(*)')
      ->where('code = ?', $value)
      ;
      
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.checkemail') ) {
      $select->where('recipient LIKE ?', $emailElement->getValue());
    }
    
    return (bool) $select->query()->fetchColumn(0);
  }
}
