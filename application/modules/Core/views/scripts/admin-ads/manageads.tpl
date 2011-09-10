<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manageads.tpl 8357 2011-02-01 01:29:21Z john $
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
  <?php echo $this->translate('Editing Ad Campaign: %1$s', $this->campaign->name) ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<h2>
  <?php echo $this->translate("Manage Advertisements") ?>
</h2>

<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINADS_MANAGEADS_DESCRIPTION") ?>
</p>

<br />



<div>
  <?php echo $this->htmlLink(array('action' => 'createad', 'id'=> $this->campaign_id, 'reset' => false),
      $this->translate("Add New Advertisement"), array(
      'class' => 'buttonlink',
      'style' => 'background-image: url(application/modules/Announcement/externals/images/admin/add.png);')) ?>
</div>

<br/>



<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s ads found", "%s ads found", $count), $count) ?>
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
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('ad_id', '<?php if($this->orderby == 'ad_id') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('name', '<?php if($this->orderby == 'name') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Name") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('views', '<?php if($this->orderby == 'views') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Views") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('clicks', '<?php if($this->orderby == 'clicks') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Clicks") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("CTR") ?>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
    <?php foreach( $this->paginator as $item ): ?>
      <tr>
        <td><?php echo $item->ad_id ?></td>
        <td style="white-space: normal;"><?php echo $item->name ?></td>
        <td><?php echo $item->views ?></td>
        <td><?php echo $item->clicks ?></td>
        <td><?php if($item->views) {echo (int)($item->clicks/$item->views*100);} else {echo 0;} ?>%</td>
        <td class="admin_table_options">
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'editad', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("edit") ?>
          </a> |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'preview', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("preview") ?>
          </a> |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'deletead', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("delete") ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

<?php else:?>

  <div class="tip">
    <span><?php echo $this->translate("There are no advertisements added to this campaign.") ?></span>
  </div>

<?php endif; ?>

