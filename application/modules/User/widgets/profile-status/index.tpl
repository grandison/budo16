<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<div id='profile_status'>
  <h2>
    <?php echo $this->subject()->getTitle() ?>
  </h2>
  <?php if( $this->auth ): ?>
    <span class="profile_status_text" id="user_profile_status_container">
      <?php echo $this->viewMore($this->subject()->status) ?>
      <?php if( !empty($this->subject()->status) && $this->subject()->isSelf($this->viewer())): ?>
        <a class="profile_status_clear" href="javascript:void(0);" onclick="en4.user.clearStatus();">(<?php echo $this->translate('clear') ?>)</a>
      <?php endif; ?>
    </span>
  <?php endif; ?>
</div>


<?php if( !$this->auth ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('This profile is private - only friends of this member may view it.');?>
    </span>
  </div>
  <br />
<?php endif; ?>