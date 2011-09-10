<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ActionType.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Form_Admin_Settings_ActionType extends Engine_Form
{

  public function init()
  {
    $this
      ->setTitle('Activity Feed Item Type Settings')
      ->setDescription('On this page you can change per item type settings. ' .
          'Start by selecting an action item type and then edit the settings. ' .
          'Note that disabling an item prevents it from being created; ' .
          'whereas an item set to not displayable will still be created, ' .
          'but will not be visible.')
      ;
    
    $this->addElement('Select', 'type', array(
      'onchange' => 'javascript:fetchActivitySettings(this.value);',
      'label' => 'Action Feed Item',
    ));

    $this->addElement('Radio', 'enabled', array(
      'label' => 'Enabled?',
      'description' => 'The other settings on this page will have ' .
        'no effect if this item is disabled.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
    ));

    $this->addElement('Radio', 'shareable', array(
      'label' => 'Shareable?',
      'description' => 'Can members share this activity feed item type?',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
    ));

    $this->addElement('MultiCheckbox', 'displayable', array(
      'label' => 'Display',
      'description' => 'Which types of feeds should this item be displayed in? The subject and object are specified in the activity item type text above.',
      'multiOptions' => array(
        4 => 'Main feed',
        2 => 'Object\'s profile feed',
        1 => 'Subject\'s profile feed',
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}