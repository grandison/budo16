<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8558 2011-03-04 01:12:51Z jung $
 * @author     Sami
 * @author     John
 */
?>

<h2>
  <?php echo $this->translate("Manage Networks") ?>
</h2>
<p>
  <?php $link = $this->htmlLink(
    array('module' => 'core', 'controller' => 'settings', 'action' => 'activity','route'=>'admin_default','reset'=>true),
    $this->translate('here')); ?>
  <?php echo $this->translate("NETWORK_VIEWS_SCRIPTS_ADMINMANAGE_INDEX_DESCRIPTION", $link) ?>
</p>

<br />

<script type="text/javascript">
  var changeOrder = function(newOrder) {
    var order = $('order').value;
    var direction = $('direction').value;
    
    if( order != newOrder ) {
      $('order').set('value', newOrder);
      $('direction').set('value', 'ASC');
    } else {
      $('order').set('value', newOrder);
      $('direction').set('value', ( direction == 'ASC' ? 'DESC' : 'ASC' ) );
    }
    $('order').getParent('form').submit();
  }
  var checkAll = function(pel) {
    var state = pel.checked;
    $$('input[type=checkbox]').each(function(el){
      el.checked = state;
    });
  }
</script>

<?php echo $this->formFilter->render($this) ?>

<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Network'), array(
    'class' => 'buttonlink',
    'style' => 'background-image: url(application/modules/Network/externals/images/admin/add.png);'
  )) ?>
</div>

<br />
<?php if( count($this->paginator) ): ?>
<?php echo $this->paginationControl($this->paginator); ?>
<br/>
<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' => 'deleteselected')) ?>'>

  <table class='admin_table'>
    <thead>
      <tr>
        <th style="width: 1%;">
          <input type='checkbox' class='checkbox' id="checkall" onchange="checkAll(this);" />
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('network_id');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('title');">
            <?php echo $this->translate("Network Name") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Related Profile Question") ?>
        </th>
        <th style="width: 1%;" class="admin_table_centered">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('member_count');">
            <?php echo $this->translate("Members") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $network ): ?>
      <tr>
        <td>
          <?php echo $this->formCheckbox('actions[]', $network->network_id) ?>
        </td>
        <td>
          <?php echo $this->locale()->toNumber($network->network_id) ?>
        </td>
        <td class="admin_table_bold">
          <?php echo $network->getTitle() ?>
        </td>
        <td>
          <?php echo $this->networkField($network, $this->fields) ?>
        </td>
        <td class="admin_table_centered">
          <?php $count = $network->getMemberCount();?>
          <?php if($count):?><a class='smoothbox' href='<?php echo $this->url(array('action' => 'members', 'network_id' => $network->network_id));?>'><?php endif;?>
            <?php
              echo $this->translate(array('%s member', '%s members', $count), $this->locale()->toNumber($count))
            ?>
          <?php if($count):?></a><?php endif;?>
        </td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(array('action' => 'edit', 'id' => $network->network_id, 'reset' => false), $this->translate('edit')) ?> |
          <?php echo $this->htmlLink(array('action' => 'delete', 'id' => $network->network_id, 'reset' => false, 'format' => 'smoothbox'), $this->translate('delete'), array('class' => 'smoothbox')) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br/>

  <div class='buttons'>
    <button type='submit'>
      <?php echo $this->translate("Delete Selected") ?>
    </button>
  </div>
</form>

<?php else:?>

  <div class="tip">
    <span>
      <?php echo $this->translate("There are currently no networks.") ?>
    </span>
  </div>

<?php endif; ?>
