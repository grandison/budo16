<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8057 2010-12-15 00:36:01Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Chat_Form_Admin_Settings_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.')
      ;

    $this->addElement('Text', 'general_delay', array(
      'label' => 'Update Frequency',
      'description' => 'CHAT_FORM_ADMIN_SETTINGS_GLOBAL_GENERALDELAY_DESCRIPTION',
      'validators' => array(
        'Int',
        array('Between', true, array(1000, 100000)),
      ),
      'value' => 8000,
    ));

    $this->addElement('Select', 'im_privacy', array(
      'label' => 'IM Privacy',
      'description' => 'Can users IM only their friends or everyone?',
      'multiOptions' => array(
        'friends' => 'Friends Only',
        'everyone' => 'Everyone',
      ),
    ));
    
    // Add submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}