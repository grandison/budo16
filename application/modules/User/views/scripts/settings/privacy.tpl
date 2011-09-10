<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: privacy.tpl 8344 2011-01-29 07:46:14Z john $
 * @author     Steve
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('My Settings');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<?php echo $this->form->render($this) ?>

<div id="blockedUserList" style="display:none;">
  <ul>
    <?php foreach ($this->blockedUsers as $user): ?>
      <?php if($user instanceof User_Model_User && $user->getIdentity()) :?>
        <li>[
          <?php echo $this->htmlLink(array('controller' => 'block', 'action' => 'remove', 'user_id' => $user->getIdentity()), 'Unblock', array('class'=>'smoothbox')) ?>
          ] <?php echo $user->getTitle() ?></li>
      <?php endif;?>
    <?php endforeach; ?>
  </ul>
</div>

<script type="text/javascript">
<!--
window.addEvent('load', function(){
  $$('#blockedUserList ul')[0].inject($('blockList-element'));
});
// -->
</script>
