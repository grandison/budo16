<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 7244 2010-09-01 01:49:53Z john $
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

<?php if( $this->isLastSuperAdmin ):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('This is the last super admin account. Please reconsider before deleting this account.'); ?>
    </span>
  </div>
<?php endif;?>

<?php echo $this->form->setAttrib('id', 'user_form_settings_delete')->render($this) ?>
