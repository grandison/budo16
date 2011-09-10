<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>



<?php if (!empty($this->code)): ?>
<div class="admin_maintenance_mode">
  <?php echo $this->translate('Your community is currently in maintenance mode and can only be accessed with a passcode: %s', "{$this->code}") ?>
  <span id="exit-maintenance-mode">
    [<a href='javascript:void(0);' onClick='exit_maintenance_mode();'><?php echo $this->translate('exit maintenance mode'); ?></a>]
  </span>
</div>

<script type="text/javascript">
//<![CDATA[
var exit_maintenance_mode = function(){
  new Request({
    url: '<?php echo $this->url(array('controller'=>'settings', 'action'=>'general'), 'admin_default') ?>',
    method: 'post',
    onRequest: function(){
      $('exit-maintenance-mode').hide();
    },
    onSuccess: function(responseText, responseXML){
      window.location.href=window.location.href;
    },
    onFailure: function(xhr){
      $('exit-maintenance-mode').show();
      //if ($type(console)) console.log('failed: %o', xhr);
    }
  }).send('maintenance_mode=0');
}
//]]>
</script>
<?php endif; ?>



<div id='global_header_right'>
  <div id='global_header_right_menu'>
    <?php if( $this->viewer()->getIdentity() ) : ?>
    <?php echo $this->htmlImage('application/modules/Core/externals/images/lock.png', '', array('class' => 'icon')) ?>
    <?php echo $this->translate("You're signed-in as %s", $this->viewer()->getTitle()) ?>
    &nbsp;
    <?php endif; ?>
    [<a href='<?php echo $this->url(array(), 'core_home') ?>'><?php echo $this->translate("back to network") ?></a>]
    &nbsp;
    [<a href='<?php echo $this->url(array(), 'user_logout') ?>'><?php echo $this->translate("sign out") ?></a>]
  </div>
</div>
