<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: edit.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<h2>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'controller' => 'language', 'action' => 'index'), $this->translate('Language Manager')) ?>
  &#187; <?php echo $this->localeTranslation ?>
</h2>
<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINLANGUAGE_EDIT_DESCRIPTION") ?>
</p>

<br />

<div class="admin_search">
  <div class="search">
    <?php echo $this->filterForm->render($this) ?>
  </div>
</div>

<br />
<?php
  $url = $this->url() . $this->query;
  if( $this->page ){
    if( !$this->query ){
      $url .=	'?';
    } else {
      $url .= '&';
    }
    $url .= "page=" . $this->page;
  }
?>
<form action="<?php echo $url ?>" method="post">
  <div>
    <div class="admin_language_editor">
      <div class="admin_language_editor_top">
        <div class="admin_language_editor_addphrase">
          <a class="buttonlink" href="javascript:void(0);" onclick="addPhrase()">Add New Phrase</a>
        </div>
        <div class="admin_language_editor_pages">
          <?php $pageInfo = $this->paginator->getPages(); if ($pageInfo->totalItemCount):  ?>
          <?php echo $this->translate('Showing %1$s-%2$s of %3$s phrases', $pageInfo->firstItemNumber, $pageInfo->lastItemNumber, $pageInfo->totalItemCount) ?>
          <?php else: ?>
            <?php echo $this->translate('No phrases found.') ?>
          <?php endif; ?>
          <span>
            <?php if( !empty($pageInfo->previous) ): ?>
              <?php echo $this->htmlLink(array('reset' => false, 'QUERY' => array_merge(array('page' => $pageInfo->previous), $this->values)), $this->translate('Previous Page')) ?>
            <?php endif; ?>
            <?php if( !empty($pageInfo->previous) && !empty($pageInfo->next) ): ?>
               |
            <?php endif; ?>
            <?php if( !empty($pageInfo->next) ): ?>
              <?php echo $this->htmlLink(array('reset' => false, 'QUERY' => array_merge(array('page' => $pageInfo->next), $this->values)), 'Next Page') ?>
            <?php endif; ?>
          </span>
        </div>
      </div>
      <ul>
        <?php $tabIndex = 1; ?>
        <?php foreach( $this->paginator as $item ): ?>
          <?php if( !$item['plural'] ): ?>
            <li>
              <?php
                $height = ceil(max(Engine_String::strlen((string)$item['current']), Engine_String::strlen((string)$item['original']), 1) / 60) * 1.2;
                echo $this->formTextarea(sprintf('values[%d]', $item['uid']), $item['current'], array('cols' => 60, 'rows' => 1, 'style' => 'height: ' . $height . 'em', 'onkeypress' => 'checkModified(this, event);'));
                echo $this->formHidden(sprintf('keys[%d]', $item['uid']), $item['key']);
              ?>
              <span class="admin_language_original">
                <?php echo $this->escape($item['original']) ?>
              </span>
            </li>
          <?php else: ?>
            <?php for( $i = 0; $i < $this->pluralFormCount; $i++ ): ?>
              <li>
                <span class="admin_language_plural">
                  <?php echo $this->translate("This phrase is pluralized. Example values:") ?> <?php echo join(', ', $this->pluralFormSample[$i]) ?>
                </span>
                <?php
                  $height = ceil(max(Engine_String::strlen((string)@$item['current'][$i]), Engine_String::strlen((string)@$item['original'][0]), 1) / 60) * 1.2;
                  echo $this->formTextarea(sprintf('values[%d][%d]', $item['uid'], $i), @$item['current'][$i], array('cols' => 60, 'rows' => 2, 'style' => 'height: ' . $height . 'em', 'onkeypress' => 'checkModified(this, event);'));
                  echo $this->formHidden(sprintf('keys[%d][%d]', $item['uid'], $i), $item['key']);
                ?>
                <span class="admin_language_original">
                  <?php echo isset($item['original'][0]) ? $this->escape($item['original'][0]) : '' ?>
                </span>
              </li>
            <?php endfor; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <div class="admin_language_editor_submit">
        <button type="submit"><?php echo $this->translate("Save Changes") ?></button
      </div>
    </div>
  </div>
</form>

<br />

<p>
   <?php echo $this->translate(
           "When you've finished editing this language pack, you can return to the %s.",
           $this->htmlLink(array('route'=>'admin_default','controller'=>'language'), 'Language Manager')) ?>
</p>
<script type="text/javascript">
//<![CDATA[
var addPhrase = function() {
  var url = '<?php echo $this->url(array('action' => 'add-phrase')) ?>';
  var phrase = prompt('Type your new phrase below:');
  var redirect = '<?php echo $this->url(array('action' => 'edit')) ?>?search=' + phrase;
  if( !phrase || phrase === null || phrase === '' ) {
    return;
  }
  var request = new Request.JSON({
    url : url,
    data : {
      phrase : phrase,
      format : 'json'
    },
    onComplete : function() {
      window.location.href = redirect;
    }
  });

  request.send();
}
//]]>
</script>