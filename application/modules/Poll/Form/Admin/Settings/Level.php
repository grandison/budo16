<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Level.php 7486 2010-09-28 03:00:23Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription('POLL_FORM_ADMIN_LEVEL_DESCRIPTION');

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Polls?',
      'description' => 'POLL_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
      'multiOptions' => array(
        2 => 'Yes, allow viewing of all polls, even private ones.',
        1 => 'Yes, allow viewing of polls.',
        0 => 'No, do not allow polls to be viewed.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if( !$this->isModerator() ) {
      unset($this->view->options[2]);
    }

    if( !$this->isPublic() ) {

      // Element: create
      $this->addElement('Radio', 'create', array(
        'label' => 'Allow Polls?',
        'description' => 'Do you want to allow members to create polls?',
        'multiOptions' => array(
          1 => 'Yes, allow this member level to create polls',
          0 => 'No, do not allow this member level to create polls',
        ),
        'value' => 1,
      ));

      // Element: edit
      $this->addElement('Radio', 'edit', array(
        'label' => 'Allow Editing of Polls?',
        'description' => 'Do you want to let members edit polls? If set to no, some other settings on this page may not apply.',
        'multiOptions' => array(
          2 => 'Yes, allow members to edit all polls.',
          1 => 'Yes, allow members to edit their own polls.',
          0 => 'No, do not allow members to edit their polls.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->edit->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
        'label' => 'Allow Deletion of Polls?',
        'description' => 'POLL_FORM_ADMIN_LEVEL_DELETE_DESCRIPTION',
        'multiOptions' => array(
          2 => 'Yes, allow members to delete all polls.',
          1 => 'Yes, allow members to delete their own polls.',
          0 => 'No, do not allow members to delete their polls.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->delete->options[2]);
      }

      // Element: comment
      $this->addElement('Radio', 'comment', array(
        'label' => 'Allow Commenting on Polls?',
        'description' => 'Do you want to let members of this level comment on polls?',
        'multiOptions' => array(
          2 => 'Yes, allow members to comment on all polls, including private ones.',
          1 => 'Yes, allow members to comment on polls.',
          0 => 'No, do not allow members to comment on polls.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->comment->options[2]);
      }

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Poll Privacy',
        'description' => 'POLL_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
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
        'label' => 'Poll Comment Options',
        'description' => 'POLL_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
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

  }
  
}