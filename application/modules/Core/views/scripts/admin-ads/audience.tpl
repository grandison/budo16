<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: audience.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){$$('th.admin_table_short input[type=checkbox]').addEvent('click', function(){ $$('input[type=checkbox]').set('checked', $(this).get('checked', false)); })});

  var changeOrder =function(orderby, direction){
    $('orderby').value = orderby;
    $('orderby_direction').value = direction;
    $('filter_form').submit();
  }


  var delectSelected =function(){
    var checkboxes = $$('input[type=checkbox]');
    var selecteditems = [];

    checkboxes.each(function(item, index){
      var checked = item.get('checked', false);
      var value = item.get('value', false);
      if (checked == true && value != 'on'){
        selecteditems.push(value);
      }
    });

    $('ids').value = selecteditems;
    $('delete_selected').submit();
    //en4.core.baseUrl+'admin/announcements/deleteselected/selected/'+selecteditems;
    //window.location = "http://www.google.com/";
  }

 function changeStatus(adcampaign_id) {
    (new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'admin-ads', 'action' => 'status'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'adcampaign_id' : adcampaign_id
      },
      'onRequest' : function(){
        $$('input[type=radio]').set('disabled', true);
      },
      'onSuccess' : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    })).send();

  }
</script>
<h2><?php echo $this->translate("Editing Ad Campaign") ?></h2>


<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<p>
  <?php echo $this->translate("Specify which members will be shown advertisements from this campaign. To include the entire member population in this campaign, leave all of the user levels and subnetworks selected. To select multiple member levels or subnetworks, use CTRL-click. Note that this advertising campaign will only be displayed to logged-in users that match both a member level AND a network you've selected.") ?>
</p>
<br/>
<form method='post'>
<table cellspacing="0" cellpadding="0" align="center">
  <tbody><tr>
  <td><b><?php echo $this->translate("User Levels") ?></b></td>
  <td style="padding-left: 10px;"><b><?php echo $this->translate("Networks") ?></b></td>
  </tr>
  <tr>
  <td>
    <select style="width: 335px;" multiple="multiple" name="ad_levels[]" class="text" size="<?php echo max(count($this->levels), count($this->networks))?>">
      <?php foreach ($this->levels as $level): ?>
        <option value="<?php echo $level->getIdentity();?>" <?php if(@in_array($level->getIdentity(), $this->selected_levels)) echo "selected";?>><?php echo $level->getTitle();?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td style="padding-left: 10px;">
    <select style="width: 335px;" multiple="multiple" name="ad_networks[]" class="text" size="<?php echo max(count($this->levels), count($this->networks))?>">
      <?php foreach ($this->networks as $network): ?>
        <option value="<?php echo $network->getIdentity();?>" <?php if(@in_array($network->getIdentity(), $this->selected_networks)) echo "selected";?>><?php echo $network->getTitle();?></option>
      <?php endforeach; ?>
    </select>
  </td>
  </tr>
  </tbody>
</table>
<br/>

<div class='buttons'>
  <button type='submit'><?php echo $this->translate("Save Settings") ?></button>
</div>
</form>