<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: createad.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */
?>
<script type="text/javascript">
//<![CDATA[
var updateTextFields = function() {
  if ($$('#mediatype-0:checked').length) {
    $('upload_image-wrapper').show();
    $('html_field-wrapper').hide();
    $('submit-wrapper').show();
  } else if ($$('#mediatype-1:checked').length) {
    $('upload_image-wrapper').hide();
    $('html_field-wrapper').show();
    $('submit-wrapper').show();
  } else {
    $('upload_image-wrapper').hide();
    $('html_field-wrapper').hide();
    $('submit-wrapper').hide();
  } 
}

var preview = function(){
  var code = $('html_code').value;
  var preview = new Element('div', {
    'html': code,
    'styles': {
        'height': 'auto',
        'width' : 'auto'
    }
  });
  //if ($type(console)) console.log(preview.getAttribute('width'));
  Smoothbox.open(preview);
}
en4.core.runonce.add(updateTextFields);
//]]>
</script>
<h2><?php echo $this->translate("Editing Ad Campaign: ") ?><?php echo $this->campaign->name;?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>