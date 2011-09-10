<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>
<?php $translate = Zend_Registry::get('Zend_Translate') ?>

<div class="admin_home_environment">
  <h3 class="sep">
    <span><?php echo $this->translate("System Mode") ?></span>
  </h3>
  <div class="admin_home_environment_buttons">
    <button onclick="changeEnvironmentMode('production', this);this.blur();"
      <?php if ('production' != APPLICATION_ENV): ?>class="button_disabled"<?php endif; ?>><?php echo $translate->_('Production Mode') ?></button>
    <button onclick="changeEnvironmentMode('development', this);this.blur();"
      <?php if ('production' == APPLICATION_ENV): ?>class="button_disabled"<?php endif; ?>><?php echo $translate->_('Development Mode') ?></button>
  </div>

  <br />

  <div class="admin_home_environment_description">
    <?php echo sprintf($translate->_('Your community is currently in %s mode.'), APPLICATION_ENV) ?>
    <?php if ('production' == APPLICATION_ENV): ?>
      <?php echo $translate->_('Most error messages are hidden and caching is enabled. If you want to make changes to your CSS layout or view scripts, please switch to Development Mode first.') ?>
    <?php else: ?>
      <?php echo $translate->_('Most error messages are shown and caching is disabled. Changes to your CSS, theme, or view scripts will appear immediately, but your system may run more slowly. Only use this mode when making changes or troubleshooting.') ?>
    <?php endif; ?>
  </div>

  <script type="text/javascript">
  //<![CDATA[
  var changeEnvironmentMode = function(mode, btn) {
    $$('div.admin_home_environment button').set('class', 'button_disabled');
    btn.set('class', '');
    $$('div.admin_home_environment_description').set('text', 'Changing mode - please wait...');
    new Request.JSON({
      url: '<?php echo $this->url(array('action'=>'change-environment-mode'), 'admin_default', true) ?>',
      method: 'post',
      onSuccess: function(responseJSON){
        if ($type(responseJSON) == 'object') {
          if (responseJSON.success || !$type(responseJSON.error))
            window.location.href = window.location.href;
          else
            alert(responseJSON.error);
        } else
          alert('An unknown error occurred; changes have not been saved.');
      }
    }).send('format=json&environment_mode='+mode);
  }
  //]]>
  </script>

</div>