<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: deleted-item.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>

<script type="text/javascript">
  parent.$('activity-item-<?php echo $this->action_id ?>').destroy();
  setTimeout(function()
  {
    parent.Smoothbox.close();
  }, <?php echo ( $this->smoothboxClose === true ? 1000 : $this->smoothboxClose ); ?>);
</script>


  <div class="global_form_popup_message">
    <?php echo $this->message ?>
  </div>