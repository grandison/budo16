<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 8536 2011-03-01 04:43:10Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Admin_Manage_Edit extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttrib('id', 'admin_members_edit')
      ->setTitle('Edit Member')
      ->setDescription('You can change the details of this member\'s account here.')
      ->setAction($_SERVER['REQUEST_URI']);

    // init email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address'
    ));

    // init username
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) > 0 ) {
      $this->addElement('Text', 'username', array(
        'label' => 'Username'
      ));
    }

    // init password
    $this->addElement('Password', 'password', array(
      'label' => 'Password',
    ));
    $this->addElement('Password', 'password_conf', array(
      'label' => 'Password Again',
    ));

    // Init level
    $levelMultiOptions = array(); //0 => ' ');
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
    foreach( $levels as $row ) {
      $levelMultiOptions[$row->level_id] = $row->getTitle();
    }
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'multiOptions' => $levelMultiOptions
    ));

    // Init level
    $networkMultiOptions = array(); //0 => ' ');
    $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
    foreach( $networks as $row ) {
      $networkMultiOptions[$row->network_id] = $row->getTitle();
    }
    $this->addElement('Multiselect', 'network_id', array(
      'label' => 'Networks',
      'multiOptions' => $networkMultiOptions
    ));

    // Init approved
    $this->addElement('Checkbox', 'approved', array(
      'label' => 'Approved?',
    ));

    // Init verified
    $this->addElement('Checkbox', 'verified', array(
      'label' => 'Verified?'
    ));

    // Init enabled
    $this->addElement('Checkbox', 'enabled', array(
      'label' => 'Enabled?',
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
    $button_group->addDecorator('DivDivDivWrapper');
  }
}