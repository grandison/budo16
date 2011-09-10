<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Form_Admin_Widget_HomePlaylist extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();

    // Set form attributes
    $this
      ->setTitle('Home Playlist')
      ->setDescription('Please choose a playlist.')
      ;
    
    // Element: poll_id
    $this->addElement('Hidden', 'playlist_id', array(
      'allowEmpty' => false,
      'required' => true,
    ));
  }
}