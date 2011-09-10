<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8189 2011-01-11 00:18:13Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'playlistsPerPage', array(
      'label' => 'Playlists Per Page',
      'description' => 'How many playlists will be shown per page? (Enter a number between 1 and 999)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('music.playlistsPerPage', 10),
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
    $values = $this->getValues();
    if (!is_numeric($values['playlistsPerPage'])
           || 0  >= $values['playlistsPerPage']
           || 999 < $values['playlistsPerPage'])
      $values['playlistsPerPage'] = 10;
    Engine_Api::_()->getApi('settings', 'core')
        ->setSetting('music.playlistsperpage', $values['playlistsPerPage']);

  }
}