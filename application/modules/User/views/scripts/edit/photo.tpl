<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: photo.tpl 7695 2010-10-23 01:26:50Z john $
 * @author     John
 */
?>

<div class="headline">
  <h2>
    <?php if ($this->viewer->isSelf($this->user) ):?>
      <?php echo $this->translate('Edit My Profile');?>
    <?php else:?>
      <?php echo $this->translate('%1$s\'s Profile', $this->htmlLink($this->user->getHref(), $this->user->getTitle()));?>
    <?php endif;?>
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
