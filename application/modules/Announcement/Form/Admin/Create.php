<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Announcement_Form_Admin_Create extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Post New Announcement')
      ->setDescription('Please compose your new announcement below.')
      ->setAttrib('id', 'announcements_create');

    // Add title
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('TinyMce', 'body', array(
      'label' => 'Body',
      'required' => true,
      'allowEmpty' => false,
    ));

        // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Post Announcement',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'announcement', 'controller' => 'manage', 'action' => 'index'), 'admin_default', true),
      'prependText' => Zend_Registry::get('Zend_Translate')->_(' or '),
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}