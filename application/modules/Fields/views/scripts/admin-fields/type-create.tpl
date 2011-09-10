<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: type-create.tpl 8111 2010-12-22 21:50:47Z char $
 * @author     John
 */
?>
<?php if( $this->form ): ?>

  <?php echo $this->form->render($this) ?>

<?php else: ?>

  <div class="global_form_popup_message">
    <?php echo $this->translate("Your changes have been saved.") ?>
  </div>

  <script type="text/javascript">
    (function() {
      parent.onTypeCreate(
        <?php echo Zend_Json::encode($this->option) ?>
      );
      parent.Smoothbox.close();
    }).delay(1000);
  </script>

<?php endif; ?>