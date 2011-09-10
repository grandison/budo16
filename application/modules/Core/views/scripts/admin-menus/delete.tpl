<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php if( $this->form ): ?>

  <?php echo $this->form->render($this) ?>

<?php elseif( $this->status ): ?>

  <div><?php echo $this->translate("Deleted") ?></div>

  <script type="text/javascript">
    var name = '<?php echo $this->name ?>';
    setTimeout(function() {
      parent.$('admin_menus_item_' + name).destroy();
      parent.Smoothbox.close();
    }, 500);
  </script>

<?php endif; ?>