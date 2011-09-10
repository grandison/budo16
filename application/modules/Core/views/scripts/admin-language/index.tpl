<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7250 2010-09-01 07:42:35Z john $
 * @author     Jung
 */
?>

<h2><?php echo $this->translate("Language Manager") ?></h2>

<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINLANGUAGE_INDEX_DESCRIPTION") ?>
</p>

<script type="text/javascript">
  var changeDefaultLanguage = function(locale) {
    var url = '<?php echo $this->url(array('module'=>'core','controller'=>'language','action'=>'default')) ?>';

    var request = new Request.JSON({
      url : url,
      data : {
        locale : locale,
        format : 'json'
      },
      onComplete : function() {
        window.location.replace( window.location.href );
      }
    });
    request.send();
  }
</script>

<br />

<div class="admin_language_options">
  <a href="<?php echo $this->url(array('action' => 'create')) ?>" class="buttonlink admin_language_options_new"><?php echo $this->translate("Create New Pack") ?></a>
  <a href="<?php echo $this->url(array('action' => 'upload')) ?>" class="buttonlink admin_language_options_upload"><?php echo $this->translate("Upload New Pack") ?></a>
</div>

<br />

<table class="admin_table admin_languages">
  <thead>
    <tr>
      <th><?php echo $this->translate("Language") ?></th>
      <th><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $this->languageNameList as $locale => $translatedLanguageTitle ): ?>
      <tr>
        <td>
          <?php echo $translatedLanguageTitle ?>
        </td>
        <td class="admin_table_options">
          <a href="<?php echo $this->url(array('action' => 'edit', 'locale' => $locale)) ?>"><?php echo $this->translate("edit phrases") ?></a>
          | <a href="<?php echo $this->url(array('action' => 'export', 'locale' => $locale)) ?>"><?php echo $this->translate("export") ?></a>
          <?php if( $this->defaultLanguage != $locale ): ?>
            | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('make default'), array('onclick' => 'changeDefaultLanguage(\'' . $locale . '\');')) ?>
            | <?php echo $this->htmlLink(array('module'=>'core','controller'=>'language','action'=>'delete',  'locale'=>$locale), $this->translate('delete'), array('class'=>'smoothbox')) ?>
          <?php else: ?>
            | <?php echo $this->translate("default") ?>
          <?php endif; ?>
          
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
