<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: create.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 * @author     John
 */
?>

<?php echo $this->partial('_formAdminJs.tpl', array('form' => $this->form)) ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
