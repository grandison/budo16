<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: spam.tpl 8765 2011-03-31 03:09:20Z john $
 * @author     Jung
 */
?>

<h2>
  <?php echo $this->translate("Spam & Banning Tools") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>