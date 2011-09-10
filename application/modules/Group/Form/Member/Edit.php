<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Form_Member_Edit extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Edit Member Title')
      ->setDescription('Enter a special title for this person.')
      ->setAttrib('id', 'group_form_title')
      ;

    //$this->addElement('Hash', 'token');

    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'label' => 'Save Changes',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'onclick' => 'parent.Smoothbox.close();',
      'prependText' => ' or ',
      'link' => true,
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}