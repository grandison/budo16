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

<h3>
  <?php echo $this->translate('Sign In or %1$sJoin%2$s', '<a href="'.$this->url(array(), "user_signup").'">', '</a>'); ?>
</h3>

<?php echo $this->form->setAttrib('class', 'global_form_box')->render($this) ?>

<?php if( !empty($this->fbUrl) ): ?>

  <script type="text/javascript">
    var openFbLogin = function() {
      Smoothbox.open('<?php echo $this->fbUrl ?>');
    }
    var redirectPostFbLogin = function() {
      window.location.href = window.location;
      Smoothbox.close();
    }
  </script>

  <?php // <button class="user_facebook_connect" onclick="openFbLogin();"></button> ?>

<?php endif; ?>