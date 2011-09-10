<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: themes.tpl 8385 2011-02-02 23:29:35Z john $
 * @author     Steve
 */
?>

<h2>
  <?php echo $this->translate("Theme Editor") ?>
</h2>

<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINTHEMES_THEMES_DESCRIPTION") ?>
</p>

<br />




<script type="text/javascript">
  var modifications = [];
  window.onbeforeunload = function() {
    if( modifications.length > 0 ) {
      return '<?php echo $this->string()->escapeJavascript($this->translate("If you leave the page now, your changes will be lost. Are you sure you want to continue?")) ?>';
    }
  }
  var pushModification = function(type) {
    modifications.push(type);
  }
  var removeModification = function(type) {
    modifications.erase(type);
  }
  var changeThemeFile = function(file) {
    var url = '<?php echo $this->url() ?>?file=' + file;
    window.location.href = url;
  }
  var saveFileChanges = function() {
    var request = new Request.JSON({
      url : '<?php echo $this->url(array('action' => 'save')) ?>',
      data : {
        'theme_id' : $('theme_id').value,
        'file' : $('file').value,
        'body' : $('body').value,
        'format' : 'json'
      },
      onComplete : function(responseJSON) {
        if( responseJSON.status ) {
          removeModification('body');
          alert('<?php echo $this->string()->escapeJavascript($this->translate("Your changes have been saved!")) ?>');
        } else {
          alert('<?php echo $this->string()->escapeJavascript($this->translate("An error has occurred. Changes could NOT be saved.")) ?>');
        }
      }
    });
    request.send();
  }
  var revertThemeFile = function() {
    var answer = confirm('<?php echo $this->string()->escapeJavascript($this->translate("CORE_VIEWS_SCRIPTS_ADMINTHEMES_THEMES_REVERTTHEMEFILE")) ?>');
    if( answer ) {
      window.location.href = '<?php echo $this->url(array('action' => 'revert')) ?>?theme_id=<?php echo $this->activeTheme->theme_id ?>';
      /*
      var request = new Request.JSON({
        url : '<?php echo $this->url(array('action' => 'revert')) ?>',
        data : {
          'theme_id' : $('theme_id').value,
          //'file' : $('file').value,
          'format' : 'json'
        },
        onComplete : function(responseJSON) {
          removeModification('body');
        }
      });
      request.send();
      */
    }
  }
  var exportThemeFile = function() {

  }
</script>

<h3>
  <?php echo $this->translate("Edit your current theme:") ?> <?php echo $this->activeTheme->title ?>
</h3>

<br />

<?php if( $this->themes[$this->active]['writable'] ): ?>

  <div class="admin_theme_editor_wrapper">
    <div class="admin_theme_editor_header">
      <div class="admin_theme_editor_header_options">
        <?php if( !empty($this->modified[$this->activeTheme->name]) ): ?>
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Restore Theme'), array('class' => 'buttonlink admin_themes_header_revert', 'onclick' => 'revertThemeFile();')) ?>
        <?php endif; ?>
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Export Theme'),  array('class' => 'buttonlink admin_themes_header_export', 'onclick' => 'exportThemeFile();')) ?>
      </div>
      <div class="admin_theme_editor_header_file">
        <?php echo $this->formSelect('choosefile', $this->active, array('onchange' => 'changeThemeFile(this.value);'), $this->activeFileOptions) ?>
      </div>
    </div>
    <form action="<?php echo $this->url(array('action' => 'save')) ?>" method="post">
      <div class="admin_theme_editor">
        <?php echo $this->formTextarea('body', $this->activeFileContents, array('onkeypress' => 'pushModification("body")', 'spellcheck' => 'false')) ?>
      </div>
      <div class="admin_theme_editor_submit">
        <button type="submit" onclick="saveFileChanges();return false;"><?php echo $this->translate("Save Changes") ?></button>
      </div>
      <?php echo $this->formHidden('file', $this->activeFileName, array()) ?>
      <?php echo $this->formHidden('theme_id', $this->activeTheme->theme_id, array()) ?>
    </form>
  </div>

<?php else: ?>

  <div class="tip">
    <span>

      <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINTHEMES_THEMES_TIP","application/themes/" . $this->active) ?>
      
    </span>
  </div>

<?php endif; ?>

<br />








<h3>
  <?php echo $this->translate('Or, pick a new theme:') ?>
</h3>

<br />

<form action="<?php echo $this->url(array('action' => 'change')) ?>" method="post">
  <div class="admin_theme_editor_wrapper">
    <ul class="admin_themes">
      <?php foreach( $this->themes as $dir => $theme ):
        // @todo meta key is deprecated and pending removal in 4.1.0; merge into main array
        $thumb = 'application/modules/Core/externals/images/anonymous.png';
        if( !empty($theme['thumb']) && file_exists("{$theme['basepath']}/{$theme['thumb']}")) {
            $thumb = "application/themes/$dir/{$theme['thumb']}";
        }
        //echo "<pre>".print_r($theme,1)."</pre>";
        ?>
        <li onclick="$(this).getElement('input[type=radio]').checked = true;">
          <div class="theme_wrapper" style="background-image: url(<?php echo $thumb ?>);">
            <?php if( $theme['modified'] ): ?>
              <span class="theme_modified">Modified</span>
            <?php endif; ?>
          </div>
          <div class="theme_name">
            <?php echo $this->formSingleRadio('theme', $theme['name'], array(
              'id' => "theme_" . $theme['name'],
              'onfocus' => 'this.blur();',
              'checked' => ( $dir === $this->active )
            )) ?>
            <label for="theme_<?php echo $theme['name'] ?>">
              <?php echo $theme['title'] ?>
            </label>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="admin_theme_editor_footer">
      <div class="admin_theme_editor_footer_options">
        <a href="javascript:void(0)" class="buttonlink admin_themes_header_import" onClick="alert('<?php echo $this->string()->escapeJavascript($this->translate('Are you sure that you want to revert all the changes you have made to this theme? If yes, the original theme will be restored immediately and your changes lost. If you want to backup your changes, export it to your computer first.')) ?>');"><?php echo $this->translate('Upload Theme') ?></a>
      </div>
      <div class="admin_theme_editor_footer_file">
        <button type="submit"><?php echo $this->translate('Save Changes') ?></button>
      </div>
    </div>
  </div>
</form>

<br />
