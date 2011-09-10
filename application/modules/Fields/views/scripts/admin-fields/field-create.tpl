<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: field-create.tpl 8410 2011-02-08 20:00:06Z char $
 * @author     John
 */
?>
<?php if( $this->form ): ?>

  <?php
    $this->headScript()
      ->appendFile($this->baseUrl().'/externals/autocompleter/Observer.js');
  ?>

  <div style="padding: 15px 15px 0px 15px;">
    <h3>
      <?php echo $this->translate('Add Profile Question') ?>
    </h3>
  </div>

  <?php if( !empty($this->formAlt) ): ?>
  <div id="select-action" style="padding: 10px 15px 0px 15px">
    <button id="create-field-button" onclick="$('create-field').setStyle('display', ''); $('map-field').setStyle('display', 'none'); $('map-field-button').addClass('admin_button_disabled'); $(this).removeClass('admin_button_disabled');">
      <?php echo $this->translate('Create New') ?>
    </button>
    <button id="map-field-button" class="admin_button_disabled" onclick="$('create-field').setStyle('display', 'none'); $('map-field').setStyle('display', ''); $('create-field-button').addClass('admin_button_disabled'); $(this).removeClass('admin_button_disabled');">
      <?php echo $this->translate('Duplicate Existing') ?>
    </button>
  </div>
  <?php endif; ?>

  <div id="create-field">
    <?php echo $this->form->render($this) ?>
  </div>

  <?php if( !empty($this->formAlt) ): ?>
    <div id="map-field" style="display: none;">
      <?php echo $this->formAlt->render($this) ?>
    </div>
  <?php endif; ?>

<?php else: ?>

  <div class="global_form_popup_message">
    <?php echo $this->translate("Your changes have been saved.") ?>
  </div>

  <script type="text/javascript">
    parent.onFieldCreate(
      <?php echo Zend_Json::encode($this->field) ?>,
      <?php echo Zend_Json::encode($this->htmlArr) ?>
    );
    (function() { parent.Smoothbox.close(); }).delay(1000);
  </script>

<?php endif; ?>