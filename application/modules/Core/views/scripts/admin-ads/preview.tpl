<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: preview.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */
?>
<div class="global_form_popup">

  <h2><?php echo $this->translate("Advertisement Preview") ?></h2><br/>
  <?php echo $this->preview?>
  <br/>
  <br/>
  <a onclick="parent.Smoothbox.close();" href="javascript:void(0);" type="button" id="cancel" name="cancel">
    <?php echo $this->translate("done") ?>
  </a>

</div>