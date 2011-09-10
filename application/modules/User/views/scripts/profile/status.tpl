<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: status.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<div id='profile_status'>
  <h2>
    <?php echo $this->subject()->getTitle() ?>
  </h2>
  <?php if( $this->auth ): ?>
    <span>
      <?php echo $this->viewMore($this->subject()->status) ?>
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