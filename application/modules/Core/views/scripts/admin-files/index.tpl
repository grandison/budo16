<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7305 2010-09-07 06:49:55Z john $
 * @author     John
 */
?>

<?php
  $baseUrl = $this->baseUrl();
  $this->headScript()
    ->appendFile($baseUrl . '/externals/fancyupload/Swiff.Uploader.js')
    ->appendFile($baseUrl . '/externals/fancyupload/Fx.ProgressBar.js')
    ->appendFile($baseUrl . '/externals/fancyupload/FancyUpload2.js');
  $this->headLink()
    ->appendStylesheet($this->baseUrl() . '/externals/fancyupload/fancyupload.css');
  $this->headTranslate(array(
    'Overall Progress ({total})', 'File Progress', 'Uploading "{name}"',
    'Upload: {bytesLoaded} with {rate}, {timeRemaining} remaining.', '{name}',
    'Remove', 'Click to remove this entry.', 'Upload failed',
    '{name} already added.',
    '{name} ({size}) is too small, the minimal file size is {fileSizeMin}.',
    '{name} ({size}) is too big, the maximal file size is {fileSizeMax}.',
    '{name} could not be added, amount of {fileListMax} files exceeded.',
    '{name} ({size}) is too big, overall filesize of {fileListSizeMax} exceeded.',
    'Server returned HTTP-Status <code>#{code}</code>',
    'Security error occurred ({text})',
    'Error caused a send or load operation to fail ({text})',
  ));
?>

<script type="text/javascript">

  var fileData = <?php echo Zend_Json::encode($this->contents) ?>;
  var absBasePath = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $this->baseUrl() . '/public/admin/'; ?>';

  var up;
  var swfPath = '<?php echo $baseUrl . '/externals/fancyupload/Swiff.Uploader.swf' ?>';
  var extraData = {
    format : 'json',
    path : '<?php echo $this->relPath ?>'
  };
  var successCount = 0;
  var failureCount = 0;
  window.addEvent('domready', function() {
    up = new FancyUpload2($('demo-status'), $('demo-list'), {
      verbose: true,
      appendCookieData: true,
      url: $('form-upload').action + '?ul=1',
      path: swfPath,
      typeFilter: {

      },
      target: 'demo-browse',
      data: extraData,
      onLoad : function() {
        $('demo-status').setStyle('display', '');
        $('demo-fallback').destroy();
        this.target.addEvents({
          click: function() {
            return false;
          },
          mouseenter: function() {
            this.addClass('hover');
          },
          mouseleave: function() {
            this.removeClass('hover');
            this.blur();
          },
          mousedown: function() {
            this.focus();
          }
        });
        $('demo-clear').addEvent('click', function() {
          up.remove(); // remove all files
          return false;
        });
      },
      onSelectFail : function(files) {
        files.each(function(file) {
          new Element('li', {
            'class': 'validation-error',
            html: file.validationErrorMessage || file.validationError,
            title: MooTools.lang.get('FancyUpload', 'removeTitle'),
            events: {
              click: function() {
                this.destroy();
              }
            }
          }).inject(this.list, 'top');
        }, this);
      },
      onComplete : function() {
        // Custom
        //window.location = window.location.href;
        $('demo-complete-message').setStyle('display', '');
      },
      onFileStart : function() {
        // @todo
      },
      onFileRemove : function(file) {
        // @todo
      },
      onSelectSuccess : function() {
        $('uploader-container').setStyle('display', '');
        $('demo-list').setStyle('display', '');
        $('demo-status-current').setStyle('display', '');
        $('demo-status-overall').setStyle('display', '');
        up.start();
      },
      onFileSuccess : function(file, response) {
        var json = new Hash(JSON.decode(response, true) || {});
        if (json.get('status') == '1') {
          successCount++;
          file.element.addClass('file-success');
          file.info.set('html', '<span><?php echo $this->translate("Upload complete.") ?></span>');
        } else {
          failureCount++;
          file.element.addClass('file-failed');
          file.info.set('html', '<span>' + (json.get('error') ? (json.get('error')) : '<?php $this->string()->escapeJavascript($this->translate('An unknown error has occurred.')) ?>')) + '</span>';
        }
      },
      onFail : function(error) {
        switch( error ) {
          case 'hidden': // works after enabling the movie and clicking refresh
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).")) ?>");
            break;
          case 'blocked': // This no *full* fail, it works after the user clicks the button
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).")) ?>");
            break;
          case 'empty': // Oh oh, wrong path
            alert("<?php echo $this->string()->escapeJavascript($this->translate("A required file was not found, please be patient and we'll fix this.")) ?>");
            break;
          case 'flash': // no flash 9+
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, install the latest Adobe Flash plugin.")) ?>");
        }
      }
    });
  });

  var fileCopyUrl = function(arg)
  {
    var fileInfo = fileData[arg];
    Smoothbox.open('<div><input type=\'text\' style=\'width:400px\' /><br /><br /><button onclick="Smoothbox.close();">Close</button></div>', {autoResize : true});
    Smoothbox.instance.content.getElement('input').set('value', absBasePath + fileInfo['rel']).focus();
    Smoothbox.instance.content.getElement('input').select();
    Smoothbox.instance.doAutoResize();
  }

  var showFallbackUploader = function()
  {
    $('uploader-container').setStyle('display', 'block');
  }

  var previewFileForceOpen;
  var previewFile = function(event)
  {
    event = new Event(event);
    element = $(event.target).getParent('.admin_file').getElement('.admin_file_preview');
    
    // Ignore ones with no preview
    if( !element || element.getChildren().length < 1 ) {
      return;
    }

    if( event.type == 'click' ) {
      if( previewFileForceOpen ) {
        previewFileForceOpen.setStyle('display', 'none');
        previewFileForceOpen = false;
      } else {
        previewFileForceOpen = element;
        previewFileForceOpen.setStyle('display', 'block');
      }
    }
    if( previewFileForceOpen ) {
      return;
    }

    var targetState = ( event.type == 'mouseover' ? true : false );
    element.setStyle('display', (targetState ? 'block' : 'none'));
  }

  window.addEvent('load', function() {
    $$('.admin_file_name').addEvents({
      click : previewFile,
      mouseout : previewFile,
      mouseover : previewFile
    });
    $$('.admin_file_preview').addEvents({
      click : previewFile
    });
  });

</script>

<h2><?php echo $this->translate("File & Media Manager") ?></h2>
<p>
  <?php echo $this->translate('You may want to quickly upload images, icons, or other media for use in your layout, announcements, blog entries, etc. You can upload and manage these files here. Move your mouse over a filename to preview an image.') ?>
</p>

<br />

<div>
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Upload New Files'), array('id' => 'demo-browse', 'class' => 'buttonlink admin_files_upload', 'onclick' => 'showFallbackUploader();')) ?>
</div>

<div id="uploader-container" class="uploader admin_files_uploader" style="display: none;">
  <div id="demo-fallback">
    <form action="<?php echo $this->url(array('action' => 'upload')) ?>" method="post" id="form-upload" enctype="multipart/form-data">
      <input type="file" name="Filedata" />
      <br />
      <br />
      <button type="submit"><?php echo $this->translate('Upload') ?></button>
      <input type="hidden" name="ul" value="1" />
      <input type="hidden" name="ut" value="standard" />
    </form>
  </div>
  <div id="demo-status" style="display: none;">
    <div style="display: none;">
      <a href="javascript:void(0);" id="demo-clear" style='display: none;'><?php $this->translate('Clear List') ?></a>
    </div>
    <div class="demo-status-overall" id="demo-status-overall" style="display:none">
      <div class="overall-title"></div>
      <img alt="" src="<?php echo $baseUrl . '/externals/fancyupload/assets/progress-bar/bar.gif' ?>" class="progress overall-progress" />
    </div>
    <div class="demo-status-current" id="demo-status-current" style="display: none">
      <div class="current-title"></div>
      <img alt="" src="<?php echo $baseUrl . '/externals/fancyupload/assets/progress-bar/bar.gif' ?>" class="progress current-progress" />
    </div>
    <div class="current-text"></div>
  </div>
  <ul id="demo-list">
  </ul>
  <div id="demo-complete-message" style="display:none;">
    <?php echo $this->htmlLink(array('reset' => false), 'Refresh the page to display new files') ?>
  </div>
</div>

<br />

<?php if(count($this->contents) > 0): $i = 0; ?>
  <div class="admin_files_wrapper">

    <iframe src="about:blank" style="display:none" name="downloadframe"></iframe>
    
    <div class="admin_files_pages">
      <?php $pageInfo = $this->paginator->getPages(); ?>
      <?php echo $this->translate(array('Showing %s-%s of %s file.', 'Showing %s-%s of %s files.', $pageInfo->totalItemCount),
          $pageInfo->firstItemNumber, $pageInfo->lastItemNumber, $pageInfo->totalItemCount) ?>
      <span>
        <?php if( !empty($pageInfo->previous) ): ?>
          <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?path=' . urlencode($this->relPath) . '&page=' . $pageInfo->previous), 'Previous Page') ?>
        <?php endif; ?>
        <?php if( !empty($pageInfo->previous) && !empty($pageInfo->next) ): ?>
           |
        <?php endif; ?>
        <?php if( !empty($pageInfo->next) ): ?>
          <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?path=' . urlencode($this->relPath) . '&page=' . $pageInfo->next), 'Next Page') ?>
        <?php endif; ?>
      </span>
    </div>

    <form action="<?php echo $this->url(array('action' => 'delete')) ?>?path=<?php echo $this->relPath ?>" method="post">
      <ul class="admin_files">
        <?php foreach( $this->paginator as $content ): $i++; $id = 'admin_file_' . $i; $contentKey = $content['rel']; ?>
          <li class="admin_file admin_file_type_<?php echo $content['type'] ?>" id="<?php echo $id ?>">
            <div class="admin_file_checkbox">
              <?php echo $this->formCheckbox('actions[]', $content['rel']) ?>
            </div>
            <div class="admin_file_options">
              <?php echo $this->htmlLink('javascript:void(0)', $this->translate('copy URL'), array('onclick' => 'fileCopyUrl(\''.$contentKey.'\');')) ?>
              | <a href="<?php echo $this->url(array('action' => 'rename', 'index' => $i)) ?>?path=<?php echo urlencode($content['rel']) ?>" class="smoothbox"><?php echo $this->translate('rename') ?></a>
              | <a href="<?php echo $this->url(array('action' => 'delete', 'index' => $i)) ?>?path=<?php echo urlencode($content['rel']) ?>" class="smoothbox"><?php echo $this->translate('delete') ?></a>
              <?php if( $content['is_file'] ): ?>
                | <a href="<?php echo $this->url(array('action' => 'download')) ?><?php echo !empty($content['rel']) ? '?path=' . urlencode($content['rel']) : '' ?>" target="downloadframe"><?php echo $this->translate('download') ?></a>
              <?php else: ?>
                | <a href="<?php echo $this->url(array('action' => 'index')) ?><?php echo !empty($content['rel']) ? '?path=' . urlencode($content['rel']) : '' ?>"><?php echo $this->translate('open') ?></a>
              <?php endif; ?>
            </div>
            <div class="admin_file_name" title="<?php echo $contentKey ?>">
              <?php if( $content['name'] == '..' ): ?>
                <?php echo $this->translate('(up)') ?>
              <?php else: ?>
                <?php echo $content['name'] ?>
              <?php endif; ?>
            </div>
            <div class="admin_file_preview admin_file_preview_<?php echo $content['type'] ?>" style="display:none">
              <?php if( $content['is_image'] ): ?>
                <?php echo $this->htmlImage($this->baseUrl() . '/public/admin/' . $content['rel'], $content['name']) ?>
              <?php elseif( $content['is_markup'] ): ?>
                <iframe style="background-color: #fff;" src="<?php echo $this->url(array('action' => 'preview')) ?>?path=<?php echo urlencode($content['rel']) ?>"></iframe>
              <?php elseif( $content['is_text'] ): ?>
                <div>
                  <?php echo nl2br($this->escape(file_get_contents($content['path']))) ?>
                </div>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="admin_files_submit">
        <button type="submit"><?php echo $this->translate('Delete Selected') ?></button>
      </div>
      <?php echo $this->formHidden('path', $this->relPath) ?>
    </form>
  </div>
<?php endif; ?>