<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: types.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  var fetchActivitySettings =function(type){
    window.location.href= en4.core.baseUrl+'admin/activity/settings/types/type/'+type;
  }
</script>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>