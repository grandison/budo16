<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Level.php 7486 2010-09-28 03:00:23Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
  
  public function init()
  {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription('MUSIC_FORM_ADMIN_LEVEL_DESCRIPTION');

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Playlists?',
      'description' => 'MUSIC_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
      'multiOptions' => array(
        2 => 'Yes, allow viewing of all playlists, even private ones.',
        1 => 'Yes, allow viewing of playlists.',
        0 => 'No, do not allow playlists to be viewed.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if( !$this->isModerator() ) {
      unset($this->view->options[2]);
    }
    
    if( !$this->isPublic() ) {
      
      // Element: create
      $this->addElement('Radio', 'create', array(
        'label' => 'Allow Music?',
        'description' => 'Do you want to allow users to upload music to their profile?',
        'multiOptions' => array(
          1 => 'Yes, allow this member level to create playlists',
          0 => 'No, do not allow this member level to create playlists',
        ),
        'value' => 1,
      ));

      // Element: edit
      $this->addElement('Radio', 'edit', array(
        'label' => 'Allow Editing of Playlists?',
        'description' => 'MUSIC_FORM_ADMIN_LEVEL_EDIT_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow members to edit all playlists.',
          1 => 'Yes, allow members to edit their own playlists.',
          0 => 'No, do not allow members to edit their playlists.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->edit->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
        'label' => 'Allow Deletion of Playlists?',
        'description' => 'MUSIC_FORM_ADMIN_LEVEL_DELETE_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow members to delete all playlists.',
          1 => 'Yes, allow members to delete their own playlists.',
          0 => 'No, do not allow members to delete their playlists.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->delete->options[2]);
      }

      // Element: comment
      $this->addElement('Radio', 'comment', array(
        'label' => 'Allow Commenting on Playlists?',
        'description' => 'Do you want to let members of this level comment on playlists?',
        'multiOptions' => array(
          2 => 'Yes, allow members to comment on all playlists, including private ones.',
          1 => 'Yes, allow members to comment on playlists.',
          0 => 'No, do not allow members to comment on playlists.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->comment->options[2]);
      }

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Playlist Privacy',
        'description' => 'MUSIC_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
        'multiOptions' => array(
          'everyone'            => 'Everyone',
          'registered'          => 'All Registered Members',
          'owner_network'       => 'Friends and Networks',
          'owner_member_member' => 'Friends of Friends',
          'owner_member'        => 'Friends Only',
          'owner'               => 'Just Me'
        ),
        'value' => array('everyone', 'owner_network', 'owner_member_member', 'owner_member', 'owner'),
      ));

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Playlist Comment Options',
        'description' => 'MUSIC_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'everyone'            => 'Everyone',
          'registered'          => 'All Registered Members',
          'owner_network'       => 'Friends and Networks',
          'owner_member_member' => 'Friends of Friends',
          'owner_member'        => 'Friends Only',
          'owner'               => 'Just Me'
        ),
        'value' => array('everyone', 'owner_network', 'owner_member_member', 'owner_member', 'owner'),
      ));

    }

    /*
    $this->addElement('Text', 'max_songs', array(
      'label' => 'Maximum Allowed Songs',
      'description' => 'MUSIC_FORM_ADMIN_LEVEL_MAXSONGS_DESCRIPTION',
      'value' => $settings->getSetting("music.maxSongsDefault", 30),
    ));

    $this->addElement('Text', 'max_filesize', array(
      'label' => 'Maximum Allowed Filesize',
      'description' => 'MUSIC_FORM_ADMIN_LEVEL_MAXFILESIZE_DESCRIPTION',
      'value' => $settings->getSetting("music.maxFilesizeDefault", 10000),
    ));

    $this->addElement('Text', 'max_storage', array(
      'label' => 'Maximum Allowed Storage',
      'description' => 'How much storage space in KB should each user have to store their files?',
      'value' => $settings->getSetting("music.maxStorageDefault", 100000),
    ));
    */
  }
}