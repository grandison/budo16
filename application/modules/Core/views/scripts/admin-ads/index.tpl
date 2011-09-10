<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8460 2011-02-15 00:37:15Z john $
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

<h2>
  <?php echo $this->translate("Manage Ad Campaigns") ?>
</h2>

<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINADS_INDEX_DESCRIPTION") ?>
</p>

<br />



<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), 
        $this->translate("Create New Campaign"),
        array('class' => 'buttonlink admin_ads_create')) ?>
</div>

<br />



<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s ad campaign found", "%s ad campaigns found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>

<br />



<?php if( count($this->paginator) ): ?>
  <table class='admin_table'>
    <thead>
      <tr>
        <th style="width: 1%;"><input type='checkbox' class='checkbox'></th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('adcampaign_id', '<?php if($this->orderby == 'adcampaign_id') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('name', '<?php if($this->orderby == 'name') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Name") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("Status") ?>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("Ads") ?>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('views', '<?php if($this->orderby == 'views') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Views") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('clicks', '<?php if($this->orderby == 'clicks') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Clicks") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("CTR") ?>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
      <tr>
        <td>
          <input type='checkbox' class='checkbox' value="<?php echo $item->adcampaign_id?>" />
        </td>
        <td>
          <?php echo $item->adcampaign_id ?>
        </td>
        <td class='admin_table_bold'>
          <?php echo $item->name ?>
        </td>
        <td class='admin_table_centered nowrap'>
          <?php echo join($this->translate(',') . '<br />', $this->status[$item->getIdentity()]) ?>
        </td>
        <td class='admin_table_centered'>
          <?php echo count($item->getAds()) ?>
        </td>
        <td class='admin_table_centered'>
          <?php echo $this->locale()->toNumber($item->views) ?>
        </td>
        <td class='admin_table_centered'>
          <?php echo $this->locale()->toNumber($item->clicks) ?>
        </td>
        <td class='admin_table_centered'>
          <?php if( $item->views <= 0 ): ?>
            <?php echo $this->translate('%s%%', $this->locale()->toNumber(0)) ?>
          <?php else: ?>
            <?php echo $this->translate('%s%%', $this->locale()->toNumber(100 * $item->clicks / $item->views)) ?>
          <?php endif ?>
        </td>
        <td class="admin_table_options">
          <a href="javascript:void(0);" onclick="javascript:changeStatus('<?php echo $item->adcampaign_id?>');">
            <?php if($item->status) echo $this->translate("pause"); else echo $this->translate("un-pause"); ?>
          </a> |
          <?php echo $this->htmlLink(array('action' => 'manageads', 'id' => $item->adcampaign_id, 'reset' => false), $this->translate("manage")) ?> |
          <?php echo $this->htmlLink(array('action' => 'edit', 'id' => $item->adcampaign_id, 'reset' => false), $this->translate("edit")) ?> |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->getIdentity())) ?>'>
            <?php echo $this->translate("delete") ?>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br />

  <div class='buttons'>
    <button onclick="javascript:delectSelected();" type='submit'><?php echo $this->translate("Delete Selected") ?></button>
  </div>

  <form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
    <input type="hidden" id="ids" name="ids" value=""/>
  </form>

<?php else:?>

  <div class="tip">
    <span><?php echo $this->translate("You currently have no advertising campaigns.") ?></span>
  </div>

<?php endif; ?>


