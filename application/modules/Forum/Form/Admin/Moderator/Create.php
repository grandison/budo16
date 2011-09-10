<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8221 2011-01-15 00:24:02Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Form_Admin_Moderator_Create extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Add Moderator')
      ->setDescription('Search for a member to add as a moderator for this forum.')
      ->setAttrib('id', 'forum_form_admin_moderator_create')
      ->setAttrib('class', 'global_form_popup')
      ;
    
    $this->addElement('Text', 'username', array(
      'label' => 'Member Name'
    ));

    $this->addElement('Hidden', 'user_id', array(
      'label' => 'User Identity',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Buttons
    $this->addElement('Button', 'execute', array(
      'label' => 'Search',
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
    
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
    //$button_group->addDecorator('DivDivDivWrapper');
  }
}