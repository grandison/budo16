<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: activity.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php
  echo $this->action('feed', 'widget', 'activity', array(
    'action_id' => $this->action_id,
    'show_comments' => (bool) $this->action_id,
    'show_likes' => (bool) $this->viewAllLikes,
  ));
  return;
?>

<form class="activity">
  <div>
    <input type="text" value="<?php echo $this->translate('Post something...');?>" />
    <p>
      <?php echo $this->htmlLink(array('route' => 'group_general', 'action' => 'create'), $this->translate('Post'), array(
        'class' => 'buttonlink icon_activity_post'
      )) ?>
    </p>
  </div>
</form>

<br />
<br />

<!--
<?php if( $this->showPost ):
  echo $this->form->setAttrib("class","global_form_box")->render($this);
endif; ?>
-->


<?php // See application/modules/activity/views/scripts/_activity*.tpl ?>
<ul class='feed'>
<?php echo $this->activityLoop($this->activity) ?>
</ul>
