<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _formAdminJs.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 * @author     John
 */
?>

<script type="text/javascript">

  window.addEvent('domready', function() {
    // Attach assignment stuff
    $$('.form-elements input[name=assignment]').addEvent('change', function(event) {
      if( !this.checked ) return;
      if( this.value == '1' ) {
        $('field_id-wrapper').setStyle('display', '');
      } else {
        $('field_id-wrapper').setStyle('display', 'none');
        $$('.network_field_container').setStyle('display', 'none');
        $('field_id').set('value', '');
      }
    }).fireEvent('change');

    // Attach field switching stuff
    $('field_id').addEvent('change', function(event) {
      var field_id = this.value;
      var field_el = $('field_pattern_' + field_id + '-wrapper');
      if( !field_el ) return;
      $$('.network_field_container').setStyle('display', 'none');
      field_el.setStyle('display', '');
    }).fireEvent('change');
  });


<?php /*
  var lastDiv = <?php echo ( $this->form->field_id->getValue() ? $this->form->field_id->getValue() : 'null' ) ?>;
  
  var updateshown = function()
  {
    if( lastDiv != null)
    {
      var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
      var display_element = document.getElementById(pattern_name).style.display='none';
    }
    lastDiv = document.getElementById('admin-form').field_id.value;
    var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
    document.getElementById(pattern_name).style.display = 'block';
  }


  var updateassign = function()
  {
    form = document.getElementById('admin-form');
    var assignment_list = form.elements['assignment'];
    var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
    var pattern_display_element = document.getElementById(pattern_name).style.display;
    var field_id_element = document.getElementById('field_id-wrapper');
   if (assignment_list[1].checked)
    { 
        document.getElementById(pattern_name).style.display = 'block';
  field_id_element.style.display = 'block';
    } 
    else 
    {
      document.getElementById(pattern_name).style.display = 'none';      
  field_id_element.style.display = 'none';

    }
  }

  window.onload = function()
  {
    updateshown();
    updateassign();
  }
  */ ?>

</script>