<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 7533 2010-10-02 09:42:49Z john $
 * @author     Sami
 * @author     John
 */
?>

<?php if( @$this->form ): ?>
  <?php echo $this->form->render($this) ?>
<?php endif; ?>

<?php if( @$this->status ): ?>
  <script type="text/javascript">
    setTimeout(function() {
      parent.Smoothbox.close();
      setTimeout(function() {
        parent.window.location.replace( parent.window.location.href );
      }, 250);
    }, 1000);
  </script>
<?php endif; ?>
