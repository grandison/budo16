<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<?php if( $this->status ): ?>

  Plan Deleted
  <script type="text/javascript">
    parent.window.location.reload();
  </script>
<?php else: ?>
  <?php echo $this->form->render($this) ?>
<?php endif; ?>
