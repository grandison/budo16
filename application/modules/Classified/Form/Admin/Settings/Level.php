<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Level.php 7486 2010-09-28 03:00:23Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription("CLASSIFIED_FORM_ADMIN_LEVEL_DESCRIPTION");

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Classifieds?',
      'description' => 'Do you want to let members view classifieds? If set to no, some other settings on this page may not apply.',
      'multiOptions' => array(
        2 => 'Yes, allow viewing of all classifieds, even private ones.',
        1 => 'Yes, allow viewing of classifieds.',
        0 => 'No, do not allow classifieds to be viewed.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if( !$this->isModerator() ) {
      unset($this->view->options[2]);
    }

    if( !$this->isPublic() ) {

      // Element: create
      $this->addElement('Radio', 'create', array(
        'label' => 'Allow Creation of Classifieds?',
        'description' => 'CLASSIFIED_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes, allow creation of classifieds.',
          0 => 'No, do not allow classifieds to be created.'
        ),
        'value' => 1,
      ));

      // Element: edit
      $this->addElement('Radio', 'edit', array(
        'label' => 'Allow Editing of Classifieds?',
        'description' => 'Do you want to let members edit classifieds? If set to no, some other settings on this page may not apply.',
        'multiOptions' => array(
          2 => 'Yes, allow members to edit all classifieds.',
          1 => 'Yes, allow members to edit their own classifieds.',
          0 => 'No, do not allow members to edit their classifieds.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->edit->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
        'label' => 'Allow Deletion of Classifieds?',
        'description' => 'Do you want to let members delete classifieds? If set to no, some other settings on this page may not apply.',
        'multiOptions' => array(
          2 => 'Yes, allow members to delete all classifieds.',
          1 => 'Yes, allow members to delete their own classifieds.',
          0 => 'No, do not allow members to delete their classifieds.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->delete->options[2]);
      }

      // Element: comment
      $this->addElement('Radio', 'comment', array(
        'label' => 'Allow Commenting on Classifieds?',
        'description' => 'Do you want to let members of this level comment on classifieds?',
        'multiOptions' => array(
          2 => 'Yes, allow members to comment on all classifieds, including private ones.',
          1 => 'Yes, allow members to comment on classifieds.',
          0 => 'No, do not allow members to comment on classifieds.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if( !$this->isModerator() ) {
        unset($this->comment->options[2]);
      }

      // Element: photo
      $this->addElement('Radio', 'photo', array(
        'label' => 'Allow Uploading of Photos?',
        'description' => 'Do you want to let members upload photos to a classified listing? If set to no, the option to upload photos will not appear.',
        'multiOptions' => array(
          1 => 'Yes, allow photo uploading to classifieds.',
          0 => 'No, do not allow photo uploading.'
        ),
        'value' => 1,
      ));

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Classifieds Listing Privacy',
        'description' => 'CLASSIFIED_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
        'multiOptions' => array(
          'everyone'            => 'Everyone',
          'registered'          => 'All Registered Members',
          'owner_network'       => 'Friends and Networks',
          'owner_member_member' => 'Friends of Friends',
          'owner_member'        => 'Friends Only',
          'owner'               => 'Just Me'
        ),
        'value' => array('everyone', 'owner_network','owner_member_member', 'owner_member', 'owner')
      ));

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Classified Comment Options',
        'description' => 'CLASSIFIED_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'description' => '',
        'multiOptions' => array(
          'everyone'            => 'Everyone',
          'registered'          => 'All Registered Members',
          'owner_network'       => 'Friends and Networks',
          'owner_member_member' => 'Friends of Friends',
          'owner_member'        => 'Friends Only',
          'owner'               => 'Just Me'
        ),
        'value' => array('everyone', 'owner_network','owner_member_member', 'owner_member', 'owner')
      ));

      // Element: max
      $this->addElement('Text', 'max', array(
        'label' => 'Maximum Allowed Classifieds',
        'description' => 'Enter the maximum number of allowed classifieds. The field must contain an integer, use zero for unlimited.',
        'validators' => array(
          array('Int', true),
          new Engine_Validate_AtLeast(0),
        ),
      ));
      
    }
  }
}