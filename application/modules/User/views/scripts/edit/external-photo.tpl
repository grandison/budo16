<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: external-photo.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>
<div style="padding: 10px;">
  <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>

  <?php echo $this->itemPhoto($this->photo) ?>
</div>