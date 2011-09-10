<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Rsvp.php 7301 2010-09-06 23:13:40Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Form_Rsvp extends Engine_Form
{
  public function init()
  {
    $this
      ->setMethod('POST')
      ->setAction($_SERVER['REQUEST_URI'])
      ;

    $this->addElement('Radio', 'rsvp', array(
      'multiOptions' => array(
        2 => 'Attending',
        1 => 'Maybe Attending',
        0 => 'Not Attending',
        //3 => 'Awaiting Reply',
      ),
    ));
  }
}