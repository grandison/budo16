<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: mail.tpl 7244 2010-09-01 01:49:53Z john $
 */
?>

<?php if( $this->form ): ?>

  <div class="settings">
    <?php echo $this->form->render($this) ?>
  </div>

<?php else: ?>

  <div class="tip">
    Your message has been queued for sending.
  </div>

<?php endif; ?>