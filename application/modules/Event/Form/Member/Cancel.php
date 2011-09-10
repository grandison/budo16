<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Cancel.php 7301 2010-09-06 23:13:40Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Form_Member_Cancel extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Cancel Invite Request')
      ->setDescription('Would you like to cancel your request for an invite to this event?')
      ->setMethod('POST')
      ->setAction($_SERVER['REQUEST_URI'])
      ;

    //$this->addElement('Hash', 'token');

    $this->addElement('Button', 'submit', array(
      'label' => 'Cancel Request',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
      'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
      'prependText' => ' or ',
      'label' => 'cancel',
      'link' => true,
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addDisplayGroup(array(
      'submit',
      'cancel'
    ), 'buttons');
  }
}