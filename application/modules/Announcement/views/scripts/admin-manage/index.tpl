<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
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
  }

</script>

<h2><?php echo $this->translate('Manage Announcements') ?></h2>
<p>
  <?php echo $this->translate('ANNOUNCEMENT_VIEW_SCRIPTS_ADMINMANAGE_DESCRIPTION', $this->url(array('module'=>'core','controller'=>'content'), 'admin_default')) ?>
</p>
<?php echo $this->formFilter->render($this) ?>

<br />

<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), 
    $this->translate("Post New Announcement"),
    array(
      'class' => 'buttonlink',
      'style' => 'background-image: url(application/modules/Announcement/externals/images/admin/add.png);')) ?>
  <?php if($this->paginator->getTotalItemCount()!=0): ?>
    <?php echo $this->translate('%d announcements total', $this->paginator->getTotalItemCount()) ?>
  <?php endif;?>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>

<br />

<?php if( count($this->paginator) ): ?>
  <table class='admin_table'>
    <thead>
      <tr>
        <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox'></th>
        <th style="width: 1%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('announcement_id', '<?php if($this->orderby == 'announcement_id') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("ID") ?>
        </a></th>
        <th><a href="javascript:void(0);" onclick="javascript:changeOrder('title', '<?php if($this->orderby == 'title') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("Title") ?>
        </a></th>
        <th style="width: 1%;"><?php echo $this->translate("Author") ?></th>
        <th style="width: 1%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', '<?php if($this->orderby == 'creation_date') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("Date") ?>
        </a></th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
      <tr>
        <td><input type='checkbox' class='checkbox' value="<?php echo $item->announcement_id?>"></td>
        <td><?php echo $item->announcement_id ?></td>
        <td class="admin_table_bold"><?php echo $item->title ?></td>
        <td><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->getTitle(), array('target' => '_blank')) ?></td>
        <td><?php echo $this->locale()->toDateTime( $item->creation_date ) ?></td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(
            array('action' => 'edit', 'id' => $item->getIdentity(), 'reset' => false),
            $this->translate('edit')) ?> |
          <?php echo $this->htmlLink(
            array('action' => 'delete', 'id' => $item->getIdentity(), 'reset' => false),
            $this->translate('delete')) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<br/>
<div class='buttons'>
  <button onclick="javascript:delectSelected();" type='submit'>
    <?php echo $this->translate("Delete Selected") ?>
  </button>
</div>

<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
  <input type="hidden" id="ids" name="ids" value=""/>
</form>

<?php else:?>

  <div class="tip">
    <span>
      <?php echo $this->translate("There are currently no announcements.") ?>
    </span>
  </div>

<?php endif; ?>
