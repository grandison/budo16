<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: verify.tpl 7341 2010-09-10 03:51:24Z john $
 * @author     Jung
 */
?>



<?php if( $this->status ): ?>

  <script type="text/javascript">
    setTimeout(function() {
      parent.window.location.href = '<?php echo $this->url(array(), 'user_login', true); ?>';
    }, 5000);
  </script>

  <?php echo $this->translate("Your account has been verified.  Please click %s to login, or wait to be redirected.",
      $this->htmlLink(array('route'=>'user_login'), $this->translate("here"))) ?>

<?php else: ?>

  <div class="error">
    <span>
      <?php echo $this->translate($this->error) ?>
    </span>
  </div>

<?php endif;