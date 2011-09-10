<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: General.php 8215 2011-01-14 07:40:36Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Settings_General extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this->setTitle('General Settings');
    $this->setDescription('These settings affect your entire community and all your members.');

    // init site maintenance mode
    $this->addElement('Radio', 'maintenance_mode', array(
      'label' => 'Maintenance Mode',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_DESCRIPTION',
      'required' => true,
      'multiOptions' => array(
        0 => 'Online',
        1 => 'Offline (Maintenance Mode)',
      ),
    ));

    // init site maintenance code
    $this->addElement('Text', 'maintenance_code', array(
      'label' => 'Maintenance Mode Code',
      'description' => 'If empty, a password will be randomly generated.',
    ));
    $this->maintenance_code->getDecorator('Description')->setOption('placement', 'append');

    // init site title
    $this->addElement('Text', 'site_title', array(
      'label' => 'Site Title',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITETITLE_DESCRIPTION'
    ));
    $this->site_title->getDecorator('Description')->setOption('placement', 'append');


    // init site description
    $this->addElement('Textarea', 'site_description', array(
      'label' => 'Site Description',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITEDESCRIPTION_DESCRIPTION'
    ));
    $this->site_description->getDecorator('Description')->setOption('placement', 'append');


    // init site keywords
    $this->addElement('Textarea', 'site_keywords', array(
      'label' => 'Site Keywords',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITEKEYWORDS_DESCRIPTION'
    ));
    $this->site_keywords->getDecorator('Description')->setOption('placement', 'append');

    // init profile
    $this->addElement('Radio', 'profile', array(
      'label' => 'Member Profiles',
      'multiOptions' => array(
        1 => 'Yes, give the public access.',
        0 => 'No, visitors must sign in to view member profiles.'
      )
    ));
    
    $this->addElement('Radio', 'browse', array(
      'label' => 'Browse Members Page',
      'required' => true,
      'multiOptions' => array(
        1 => 'Yes, give the public access.',
        0 => 'No, visitors must sign in to view the browse members page.'
      )
    ));

    $this->addElement('Radio', 'search', array(
      'label' => 'Search Page',
      'required' => true,
      'multiOptions' => array(
        1 => 'Yes, give the public access.',
        0 => 'No, visitors must sign in to view the search page.'
      )
    ));

    $this->addElement('Radio', 'portal', array(
      'label' => 'Portal Page',
      'required' => true,
      'multiOptions' => array(
        1 => 'Yes, give the public access.',
        0 => 'No, visitors must sign in to view the main portal page.'
      )
    ));

    $this->addElement('Select', 'quota', array(
      'label' => 'Storage Quota',
      'required' => true,
      'multiOptions' => Engine_Api::_()->getItemTable('storage_file')->getStorageLimits(),
      'value' => 52428800,
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_QUOTA_DESCRIPTION'
    ));

    $this->addElement('Select', 'notificationupdate', array(
      'label' => 'Notification Update Frequency',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_NOTIFICATIONUPDATE_DESCRIPTION',
      'value' => 120000,
      'multiOptions' => array(
        30000  => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION1',
        60000  => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION2',
        120000 => "ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION3",
        0      => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION4'
      )
    ));
    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}