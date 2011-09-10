<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: General.php 8344 2011-01-29 07:46:14Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Settings_General extends Engine_Form
{
  protected $_item;

  public function setItem(User_Model_User $item)
  {
    $this->_item = $item;
  }

  public function getItem()
  {
    if( null === $this->_item ) {
      throw new User_Model_Exception('No item set in ' . get_class($this));
    }

    return $this->_item;
  }

  public function init()
  {
    // @todo fix form CSS/decorators
    // @todo replace fake values with real values
    $this->setTitle('General Settings')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Init email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true),
        array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix().'users', 'email', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
      ),
    ));
    $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    $this->email->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');

    // Init username
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) > 0 ) {
      $this->addElement('Text', 'username', array(
        'label' => 'Profile Address',
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('Alnum', true),
          array('StringLength', true, array(4, 64)),
          array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix().'users', 'username', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
        ),
      ));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid profile address.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this profile address, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Profile addresses must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Profile addresses must be alphanumeric.', 'notAlnum');
    }
    
    // Init type
    $this->addElement('Select', 'accountType', array(
      'label' => 'Account Type',
    ));

    // Init Facebook
    $facebook_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_facebook_enable', 'none');
    if ('none' != $facebook_enable) {
      $and_publish = ('publish' == $facebook_enable)
                   ? ' and publish content to your Facebook wall.'
                   : '.';
      $this->addElement('Dummy', 'facebook', array(
        'label' => 'Facebook Integration',
        'description' => 'Linking your Facebook account will let you login with Facebook'.$and_publish,
        'content' => User_Model_DbTable_Facebook::loginButton('Integrate with my Facebook'),
      ));
      $this->addElement('Checkbox', 'facebook_id', array(
        'label' => 'Integrate with my Facebook',
        'description' => 'Facebook Integration',
      ));
    }

    // Init timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Timezone',
      'description' => 'Select the city closest to you that shares your same timezone.',
      'multiOptions' => array(
        'US/Pacific'  => '(UTC-8) Pacific Time (US & Canada)',
        'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
        'US/Central'  => '(UTC-6) Central Time (US & Canada)',
        'US/Eastern'  => '(UTC-5) Eastern Time (US & Canada)',
        'America/Halifax'   => '(UTC-4)  Atlantic Time (Canada)',
        'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
        'Pacific/Honolulu'  => '(UTC-10) Hawaii (US)',
        'Pacific/Samoa'     => '(UTC-11) Midway Island, Samoa',
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
        'India/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
        'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
        'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
        'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
        'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
        'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
        'Asia/Magadan' => '(UTC+11) Magadan, Soloman Is., New Caledonia',
        'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
      ),
    ));

    // Init default locale
    $locale = Zend_Registry::get('Locale');

    $localeMultiKeys = array_merge(
      array_keys(Zend_Locale::getLocaleList())
    );
    $localeMultiOptions = array();
    $languages = Zend_Locale::getTranslationList('language', $locale);
    $territories = Zend_Locale::getTranslationList('territory', $locale);
    foreach($localeMultiKeys as $key)
    {     
       if (!empty($languages[$key])) 
       {
         $localeMultiOptions[$key] = $languages[$key];
       }
       else
       {
         $locale = new Zend_Locale($key);
         $region = $locale->getRegion();
         $language = $locale->getLanguage(); 
         if ((!empty($languages[$language]) && (!empty($territories[$region])))) {
           $localeMultiOptions[$key] =  $languages[$language] . ' (' . $territories[$region] . ')';
         }
       }
    }
    $localeMultiOptions = array_merge(array('auto'=>'[Automatic]'), $localeMultiOptions);
    
    $this->addElement('Select', 'locale', array(
      'label' => 'Locale',
      'description' => 'Dates, times, and other settings will be displayed using this locale setting.',
      'multiOptions' => $localeMultiOptions
    ));

    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
    
    // Create display group for buttons
    #$this->addDisplayGroup($emailAlerts, 'checkboxes');

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
       'module' => 'user',
       'controller' => 'settings',
       'action' => 'general',
    ), 'default'));
  }
}