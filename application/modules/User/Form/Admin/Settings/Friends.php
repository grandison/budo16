<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Friends.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Admin_Settings_Friends extends Engine_Form
{
  public function init()
  {
    $friend_settings = Engine_Api::_()->getApi('settings', 'core')->user_friends;
    $this->setTitle('Friendship Settings');
    $this->setDescription('USER_FORM_ADMIN_SETTINGS_FRIENDS_DESCRIPTION');


    $eligible = new Engine_Form_Element_Radio('eligible');
    $eligible->setLabel("Who Can Become Friends");
    $eligible->setDescription('USER_FORM_ADMIN_SETTINGS_ELIGIBLE_DESCRIPTION');
    $eligible->addMultiOptions(Array('2'=>'All Members', '1'=>'Only members in their networks', '0'=>'Nobody'));
    $eligible->setValue($friend_settings['eligible']);
    $direction = new Engine_Form_Element_Radio('direction');
    $direction->setLabel("Friendship Direction");
    $direction->setDescription("USER_FORM_ADMIN_SETTINGS_DIRECTION_DESCRIPTION");
    $direction->addMultiOptions(Array('1'=>'Two-way Friendships', '0'=>'One-way Friendships'));
    $direction->setValue($friend_settings['direction']);

    $verification = new Engine_Form_Element_Radio('verification');
    $verification->setLabel("Friendship Verification");
    $verification->setDescription("USER_FORM_ADMIN_SETTINGS_VERIFICATION_DESCRIPTION");
    $verification->addMultiOptions(Array('1'=>"Verified Friendships", '0'=>"Unverified Friendships"));
    $verification->setValue($friend_settings['verification']);

    $lists = new Engine_Form_Element_Radio('lists');
    $lists->setLabel("Friend Lists");
    $lists->setDescription('USER_FORM_ADMIN_SETTINGS_LISTS_DESCRIPTION');
    $lists->addMultiOptions(Array('1'=>"Yes, users can group their friends into lists", '0'=>"No, do not allow friend lists"));
    $lists->setValue($friend_settings['lists']);
    $submit = new Engine_Form_Element_Button('submit', Array('label'=>'Save Changes', 'ignore'=>true, 'type'=>'submit'));
    $this->addElements(Array($eligible, $direction, $verification, $lists, $submit));
  }

  public function saveValues()
  {
    $values = $this->getValues();
    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();
    try
    {
      $table = Engine_Api::_()->getDbtable('membership', 'user');
      $select = $table->select();



      // handle directional change
      // get the current directional
      // if direction 0 && the value['direction'] is == 1
      // we must make sure all the one way ones have an opposite
      $direction = Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction');
      if($values['direction']== 1 && $direction ==0){
        $direction_select = $select;
        // go through the friendships and make active, resource_approved, user_approved into 1
        foreach( $direction_select->getTable()->fetchAll($direction_select) as $friendship )
        {
          $direction_select = $table->select()->where('resource_id = ?', $friendship->user_id)->where('user_id = ?', $friendship->resource_id);
          $row = $direction_select->getTable()->fetchRow($direction_select);
          if(!$row){
            $new_friendship = $table->createRow();
            $new_friendship->resource_id = $friendship->user_id;
            $new_friendship->user_id = $friendship->resource_id;
            $new_friendship->active = $friendship->active;
            $new_friendship->resource_approved = $friendship->resource_approved;
            $new_friendship->user_approved = $friendship->user_approved;
            $new_friendship->message = $friendship->message;
            $new_friendship->description = $friendship->description;
            $new_friendship->save();
          }
        }
      }



      // handle verification change
      // if verification ==0 and changes to ==1
      // we must make all pending requests active
      $verification = Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.verification');
      if($values['verification']==0 && $verification ==1){
        // select all where active ==0
        $verification_select = $table->select()->where('active = ?', 0);

        // go through the friendships and make active, resource_approved, user_approved into 1
        foreach( $verification_select->getTable()->fetchAll($verification_select) as $friendship )
        {
          $friendship->active =1;
          $friendship->resource_approved =1;
          $friendship->user_approved =1;
          $friendship->save();
        }
      }

      Engine_Api::_()->getApi('settings', 'core')->setSetting("user.friends.verification", $values['verification']);
      Engine_Api::_()->getApi('settings', 'core')->setSetting("user.friends.direction", $values['direction']);

      $settings = Engine_Api::_()->getApi('settings', 'core');
      $settings->user_friends = $values;
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
       throw $e;
      return;
    }
  }
  

}
?>
