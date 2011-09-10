<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: contact.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php if( $this->status ): ?>
  <?php echo $this->message; ?>
<?php else: ?>
  <?php echo $this->form->render($this) ?>
<?php endif; ?>