<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _FancyUpload.tpl 7305 2010-09-07 06:49:55Z john $
 * @author     Sami
 */
?>

<?php
$this->headScript()
    ->appendFile($this->baseUrl() . '/externals/fancyupload/Swiff.Uploader.js')
    ->appendFile($this->baseUrl() . '/externals/fancyupload/Fx.ProgressBar.js')
    ->appendFile($this->baseUrl() . '/externals/fancyupload/FancyUpload2.js');
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
var uploadCount = 0;
var extraData = <?php echo $this->jsonInline($this->data); ?>;
//var extraData = {"classified_id":15};

window.addEvent('domready', function() { // wait for the content
  // our uploader instance

  var up = new FancyUpload2($('demo-status'), $('demo-list'), { // options object
    // we console.log infos, remove that in production!!
    verbose: false,
    multiple: false,
    appendCookieData: true,

    // url is read from the form, so you just have to change one place
    url: $('form-upload').action + '?ul=1',

    // path to the SWF file
    path: '<?php echo $this->baseUrl() . '/externals/fancyupload/Swiff.Uploader.swf';?>',

    // remove that line to select all files, or edit it, add more items
    typeFilter: {
      'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
    },

    // this is our browse button, *target* is overlayed with the Flash movie
    target: 'demo-browse',

                data: extraData,

    // graceful degradation, onLoad is only called if all went well with Flash
    onLoad: function() {
      $('demo-status').removeClass('hide'); // we show the actual UI
      $('demo-fallback').destroy(); // ... and hide the plain form

      // We relay the interactions with the overlayed flash to the link
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

      // Interactions for the 2 other buttons

      $('demo-clear').addEvent('click', function() {
        up.remove(); // remove all files
        var fileids = document.getElementById('fancyuploadfileids');

        fileids.value ="";
        return false;
      });

    },

    // Edit the following lines, it is your custom event handling

    /**
     * Is called when files were not added, "files" is an array of invalid File classes.
     *
     * This example creates a list of error elements directly in the file list, which
     * hide on click.
     */
    onSelectFail: function(files) {
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

    onComplete: function hideProgress() {
      var demostatuscurrent = document.getElementById("demo-status-current");
      var demostatusoverall = document.getElementById("demo-status-overall");
      var demosubmit = document.getElementById("submit-wrapper");

      demostatuscurrent.style.display = "none";
      demostatusoverall.style.display = "none";
      demosubmit.style.display = "block";
    },

    onFileStart: function() {
      uploadCount += 1;
                  $('demo-browse').style.display = "none";
                  $('demo-clear').style.display = "none";
    },
                onFileRemove: function(file) {
                    uploadCount -= 1;
                    file_id = file.photo_id;
                    request = new Request.JSON({
                     'format' : 'json',
                     'url' : '<?php echo $this->url(Array('module'=>'core', 'controller' => 'admin-ads', 'action'=>'removephoto'), 'default') ?>',
                     'data': {
                       'photo_id' : file_id
                     },
                    'onSuccess' : function(responseJSON) {
                      var html_code_element = document.getElementById("html_field-wrapper");
                      html_code_element.style.display = "none";
                      $('demo-browse').style.display = "block";

                      return false;
                    }
                  });

                  request.send();
                  var fileids = document.getElementById('fancyuploadfileids');

      if (uploadCount == 0)
      {
            var democlear = document.getElementById("demo-clear");
            var demolist = document.getElementById("demo-list");
        var demosubmit = document.getElementById("submit-wrapper");
        democlear.style.display = "none";
        demolist.style.display = "none";
        demosubmit.style.display = "none";
      }
      fileids.value = fileids.value.replace(file_id, "");
    },
    onSelectSuccess: function(file) {
                  $('demo-list').style.display = 'block';
      var democlear = document.getElementById("demo-clear");
      var demostatuscurrent = document.getElementById("demo-status-current");
      var demostatusoverall = document.getElementById("demo-status-overall");

      democlear.style.display = "inline";
      demostatuscurrent.style.display = "block";
      demostatusoverall.style.display = "block";
                  up.start();
    } ,
    /**
     * This one was directly in FancyUpload2 before, the event makes it
     * easier for you, to add your own response handling (you probably want
     * to send something else than JSON or different items).
     */
    onFileSuccess: function(file, response) {
      var json = new Hash(JSON.decode(response, true) || {});
                        
      if (json.get('status') == '1') {
        file.element.addClass('file-success');
        file.info.set('html', '<span>Upload complete.</span>');
        var fileids = document.getElementById('fancyuploadfileids');
        fileids.value = fileids.value + json.get('photo_id') + " ";
                                $('photo_id').value =  json.get('photo_id');
        file.photo_id = json.get('photo_id');

                                // show the html code element and populate with uploaded image html
                                var html_code_element = document.getElementById("html_field-wrapper");
                                html_code_element.style.display = "block";
                                $('html_code').value = json.get('photo_url');
      } else {
        file.element.addClass('file-failed');
        file.info.set('html', '<span>An error occurred:</span> ' + (json.get('error') ? (json.get('error')) : response));
      }
    },

    /**
     * onFail is called when the Flash movie got bashed by some browser plugin
     * like Adblock or Flashblock.
     */
    onFail: function(error) {
      switch (error) {
        case 'hidden': // works after enabling the movie and clicking refresh
          alert('<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).")) ?>');
          break;
        case 'blocked': // This no *full* fail, it works after the user clicks the button
          alert('<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).")) ?>');
          break;
        case 'empty': // Oh oh, wrong path
          alert('<?php echo $this->string()->escapeJavascript($this->translate("A required file was not found, please be patient and we'll fix this.")) ?>');
          break;
        case 'flash': // no flash 9+
          alert('<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, install the latest Adobe Flash plugin.")) ?>');
      }
    }

  });

});
</script>

<input type="hidden" name="<?php echo $this->name;?>" id="fancyuploadfileids" value ="" />
<fieldset id="demo-fallback">
  <legend><?php echo $this->translate('File Upload');?></legend>
  <p>
    <?php echo $this->translate("CORE_VIEWS_SCRIPTS_FANCYUPLOAD_DESCRIPTION");?>
  </p>
  <label for="demo-photoupload">
    <?php echo $this->translate('Upload a Photo:');?>
    <input type="file" name="Filedata" />
  </label>
</fieldset>

<div id="demo-status" class="hide">
  <div>
    <?php echo $this->translate('CORE_VIEWS_SCRIPTS_FANCYUPLOAD_ADDPHOTOS');?>
  </div>
  <div>
    <a class="buttonlink icon_photos_new" href="javascript:void(0);" id="demo-browse"><?php echo $this->translate('Add Photos');?></a>
    <a class="buttonlink icon_clearlist" href="javascript:void(0);" id="demo-clear" style='display: none;'><?php echo $this->translate('Clear List');?></a>
  </div>
  <div class="demo-status-overall" id="demo-status-overall" style="display: none">
    <div class="overall-title"></div>
    <img src="<?php echo $this->baseUrl() . '/externals/fancyupload/assets/progress-bar/bar.gif';?>" class="progress overall-progress" />
  </div>
  <div class="demo-status-current" id="demo-status-current" style="display: none">
    <div class="current-title"></div>
    <img src="<?php echo $this->baseUrl() . '/externals/fancyupload/assets/progress-bar/bar.gif';?>" class="progress current-progress" />
  </div>
  <div class="current-text"></div>
</div>
<ul id="demo-list"></ul>

