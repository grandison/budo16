<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: success.tpl 7399 2010-09-16 01:48:24Z john $
 * @author     John
 */
?>

<div>
  <?php if( $this->parentRefresh ): // Refresh parent window (for smoothboxes) ?>
    <script type="text/javascript">
      setTimeout(function()
      {
        parent.window.location.reload( false );
      }, <?php echo ( $this->parentRefresh === true ? 1000 : $this->parentRefresh ); ?>);
    </script>
  <?php endif; ?>

  <?php if( $this->parentRedirect ): // Refresh parent window (for smoothboxes) ?>
    <script type="text/javascript">
      setTimeout(function()
      {
        parent.window.location.href = '<?php echo $this->parentRedirect ?>';
      }, <?php echo ( empty($this->parentRedirectTime) ? 1000 : $this->parentRedirectTime ); ?>);
    </script>
  <?php endif; ?>

  <?php if( $this->smoothboxClose ): // Close smoothbox (for smoothboxes) ?>
    <script type="text/javascript">
      setTimeout(function()
      {
        parent.Smoothbox.close();
      }, <?php echo ( $this->smoothboxClose === true ? 1000 : $this->smoothboxClose ); ?>);
    </script>
  <?php endif; ?>

  <?php if( $this->redirect ): ?>
    <script type="text/javascript">
      setTimeout(function()
      {
        window.location.href = '<?php echo $this->redirect ?>';
      }, <?php echo ( isset($this->redirectTime) ? $this->redirectTime : 500 ); ?>);
    </script>
  <?php endif; ?>

  <?php foreach( $this->messages as $message ): // Show messages ?>
    <div class="global_form_popup_message">
      <?php echo $message ?>
    </div>
  <?php endforeach; ?>
</div>