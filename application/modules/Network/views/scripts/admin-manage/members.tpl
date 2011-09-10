<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Jung
 */
?>
<div class="global_form_popup">
  <div class="admin_member_list">
  <h3><?php echo $this->translate('Network Members');?></h3>
  <ul>
    <?php foreach( $this->members as $membership ):
      if( !isset($this->memberUsers[$membership->user_id]) ) continue;
      $member = $this->memberUsers[$membership->user_id];
      ?>
      <li>
        <?php echo $this->htmlLink($member->getHref(), $this->itemPhoto($member, 'thumb.icon'), array('class' => 'profile_friends_icon')) ?>
        <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>
      </li>
    <?php endforeach;?>
  </ul>
  </div>
  <br/>
  <?php echo $this->paginationControl($this->members, null, null, array(
      'query' => array('format'=>'smoothbox'))); ?>
  <br/>
  <button type="submit" onclick="parent.Smoothbox.close();return false;" name="close_button" value="Close">Close</button>

</div>
<br/>
