<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8064 2010-12-15 23:10:34Z char $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'perPage', array(
      'label' => 'Polls Per Page',
      'description' => 'How many polls will be shown per page? (Enter a number between 1 and 999)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.perPage', 10),
    ));

    $this->addElement('Text', 'maxOptions', array(
      'label' => 'Maximum Options',
      'description' => 'How many possible poll answers do you want to permit?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.maxOptions', 15),
    ));

    $this->addElement('Radio', 'canChangeVote', array(
      'label' => 'Change Vote?',
      'description' => 'Do you want to permit your members to change their vote?',
      'multiOptions' => array(
        1 => 'Yes, members can change their vote.',
        0 => 'No, members cannot change their vote.',
      ),
      'value' => (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canChangeVote', false),
    ));


    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }

  public function saveValues()
  {
    $values   = $this->getValues();
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $settings->poll_canChangeVote = (bool) $values['canChangeVote'];

    if (!is_numeric($values['perPage'])
            || 0 >= $values['perPage']
            || 999 < $values['perPage'])
      $values['perPage'] = 10;
    $settings->poll_perPage = $values['perPage'];

    if (!is_numeric($values['maxOptions'])
            || 0 >= $values['maxOptions']
            || 999 < $values['maxOptions'])
      $values['maxOptions'] = 15;
    $settings->poll_maxOptions = $values['maxOptions'];
    
  }
}