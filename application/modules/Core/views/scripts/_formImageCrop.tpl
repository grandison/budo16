<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _formImageCrop.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php
/**
 * Image crop/selection
 * Params:
 *   subject (OPTIONAL)
 */

// Detect subject
$subject = null;
if( !empty($this->subject) ) $subject = $this->subject;
else $subject = $this->subject();
if( !($subject instanceof Core_Model_Item_Abstract) || !$subject->getIdentity() || !isset($subject->photo_id) ) {
  return;
}

// Add required scripts
$this->headScript()
  ->appendFile($this->baseUrl().'/externals/moolasso/Lasso.js')
  ->appendFile($this->baseUrl().'/externals/moolasso/Lasso.Crop.js')
?>

<div>
  <?php echo $this->itemPhoto($this->subject(), 'thumb.profile', "", array('id' => 'lassoImg')) ?>
</div>
<br />
<div id="preview-thumbnail" class="preview-thumbnail">
  <?php echo $this->itemPhoto($this->subject(), 'thumb.icon', "", array('id' => 'previewimage')) ?>
</div>
<div id="thumbnail-controller" class="thumbnail-controller">
  <?php if ($this->subject()->getPhotoUrl()): ?>
  <a href="javascript:void(0);" onclick="lassoStart();"><?php echo $this->translate('Edit Thumbnail') ?></a>
  <?php endif; ?>
</div>
<script type="text/javascript">
  var orginalThumbSrc;
  var originalSize;
  var loader = new Element('img',{ src:'application/modules/Core/externals/images/loading.gif'});
  var lassoCrop;

  var lassoSetCoords = function(coords)
  {
    var delta = (coords.w - 48) / coords.w;

    $('coordinates').value =
      coords.x + ':' + coords.y + ':' + coords.w + ':' + coords.h;

    $('previewimage').setStyles({
      top : -( coords.y - (coords.y * delta) ),
      left : -( coords.x - (coords.x * delta) ),
      height : ( originalSize.y - (originalSize.y * delta) ),
      width : ( originalSize.x - (originalSize.x * delta) )
    });
  }

  var lassoStart = function()
  {
    if( !orginalThumbSrc ) orginalThumbSrc = $('previewimage').src;
    originalSize = $("lassoImg").getSize();

    lassoCrop = new Lasso.Crop('lassoImg', {
      ratio : [1, 1],
      preset : [10,10,58,58],
      min : [48,48],
      handleSize : 8,
      opacity : .6,
      color : '#7389AE',
      border : '<?php echo $this->baseUrl().'/externals/moolasso/crop.gif' ?>',
      onResize : lassoSetCoords,
      bgimage : ''
    });

    $('previewimage').src = $('lassoImg').src;
    $('thumbnail-controller').innerHTML = '<a href="javascript:void(0);" onclick="lassoEnd();"><?php echo $this->translate('Apply Changes') ?></a> <?php echo $this->translate('or') ?> <a href="javascript:void(0);" onclick="lassoCancel();"><?php echo $this->translate('cancel') ?></a>';
    $('coordinates').value = 10 + ':' + 10 + ':' + 58+ ':' + 58;
  }

  var lassoEnd = function() {
    $('thumbnail-controller').innerHTML = "<div><img class='loading_icon' src='application/modules/Core/externals/images/loading.gif' /><?php echo $this->translate(' Loading...') ?></div>";
    lassoCrop.destroy();
    $('EditPhoto').submit();
  }

  var lassoCancel = function() {
    $('preview-thumbnail').innerHTML = '<img id="previewimage" src="'+orginalThumbSrc+'"/>';
    $('thumbnail-controller').innerHTML = '<a href="javascript:void(0);" onclick="lassoStart();"><?php echo $this->translate('Edit Thumbnail') ?></a>';
    $('coordinates').value = "";
    lassoCrop.destroy();
  }

  var uploadSignupPhoto = function() {
    $('thumbnail-controller').innerHTML = "<div><img class='loading_icon' src='application/modules/Core/externals/images/loading.gif' /><?php echo $this->translate(' Loading...') ?></div>";
    $('EditPhoto').submit();
    $('Filedata-wrapper').innerHTML = "";
  }
</script>
