<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Level.php 7943 2010-12-05 21:14:24Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription('GROUP_FORM_ADMIN_LEVEL_DESCRIPTION');

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Groups?',
      'description' => 'GROUP_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
      'multiOptions' => array(
        2 => 'Yes, allow members to view all groups, even private ones.',
        1 => 'Yes, allow viewing and subscription of groups.',
        0 => 'No, do not allow groups to be viewed.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if( !$this->isModerator() ) {
      unset($this->view->options[2]);
    }

    if( !$this->isPublic() ) {

      // Element: create
      $this->addElement('Radio', 'create', array(
        'label' => 'Allow Creation of Groups?',
        'description' => 'GROUP_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes, allow creation of groups.',
          0 => 'No, do not allow groups to be created.',
        ),
        'value' => 1,
      ));

      // Element: edit
      $this->addElement('Radio', 'edit', array(
        'label' => 'Allow Editing of Groups?',
        'description' => 'Do you want to let users edit and delete groups?',
        'multiOptions' => array(
          2 => 'Yes, allow members to edit everyone\'s groups.',
          1 => 'Yes, allow  members to edit their own groups.',
          0 => 'No, do not allow groups to be edited.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->edit->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
        'label' => 'Allow Deletion of Groups?',
        'description' => 'Do you want to let members delete groups? If set to no, some other settings on this page may not apply.',
        'multiOptions' => array(
          2 => 'Yes, allow members to delete all groups.',
          1 => 'Yes, allow members to delete their own groups.',
          0 => 'No, do not allow members to delete their groups.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->delete->options[2]);
      }

      // Element: comment
      $this->addElement('Radio', 'comment', array(
        'label' => 'Allow Commenting on Groups?',
        'description' => 'Do you want to let members of this level comment on groups?',
        'multiOptions' => array(
          2 => 'Yes, allow members to comment on all groups, including private ones.',
          1 => 'Yes, allow members to comment on groups.',
          0 => 'No, do not allow members to comment on groups.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->comment->options[2]);
      }

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Group Privacy',
        'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
        'multiOptions' => array(
          'everyone' => 'Everyone',
          'registered' => 'Registered Members',
          'member' => 'Members Only',
          //'officer' => 'Officers and Owner Only',
          //'owner' => 'Owner Only'
        )
      ));

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Group Posting Options',
        'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'registered' => 'Registered Members',
          'member' => 'All Members',
          'officer' => 'Officers and Owner Only',
          //'owner' => 'Owner Only',
        )
      ));

      // Element: auth_photo
      $this->addElement('MultiCheckbox', 'auth_photo', array(
        'label' => 'Photo Upload Options',
        'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHPHOTO_DESCRIPTION',
        'multiOptions' => array(
          'registered' => 'Registered Members',
          'member' => 'All Members',
          'officer' => 'Officers and Owner Only',
          //'owner' => 'Owner Only',
        )
      ));

      // Element: auth_event
      $this->addElement('MultiCheckbox', 'auth_event', array(
        'label' => 'Event Creation Options',
        'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHEVENT_DESCRIPTION',
        'multiOptions' => array(
          'registered' => 'Registered Members',
          'member' => 'All Members',
          'officer' => 'Officers and Owner Only',
          //'owner' => 'Owner Only',
        )
      ));

      // Element: style
      $this->addElement('Radio', 'style', array(
        'label' => 'Allow Group Style',
        'required' => true,
        'multiOptions' => array(
          1 => 'Yes, allow custom group styles.',
          0 => 'No, do not allow custom group styles.'
        ),
        'value' => 1,
      ));
    }

  }

}