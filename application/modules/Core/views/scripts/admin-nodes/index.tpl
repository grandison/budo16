<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2><?php echo $this->translate("Server Nodes") ?></h2>

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
  <?php echo (
    'CORE_VIEWS_SCRIPTS_ADMINNODES_INDEX_DESCRIPTION' !== ($desc = $this->translate("CORE_VIEWS_SCRIPTS_ADMINNODES_INDEX_DESCRIPTION")) ?
    $desc : '' ) ?>
</p>

<br />


<?php if( !empty($this->formFilter) ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />
<?php endif ?>



<?php if( $this->paginator->count() > 1 ): ?>
  <?php echo $this->paginationControl($this->paginator) ?>
  <br />
<?php endif; ?>


<table class="admin_table">
  <thead>
    <tr>
      <th style="width: 1%;">
        <a href="javascript:void(0)" onclick="handleSort('node_id')">
          <?php echo $this->translate('ID') ?>
        </a>
      </th>
      <th>
        <a href="javascript:void(0)" onclick="handleSort('signature')">
          <?php echo $this->translate('Signature') ?>
        </a>
      </th>
      <th style="width: 1%;">
        <a href="javascript:void(0)" onclick="handleSort('host')">
          <?php echo $this->translate('Host') ?>
        </a>
      </th>
      <th style="width: 1%;">
        <a href="javascript:void(0)" onclick="handleSort('ip')">
          <?php echo $this->translate('IP') ?>
        </a>
      </th>
      <th style="width: 1%;">
        <a href="javascript:void(0)" onclick="handleSort('first_seen')">
          <?php echo $this->translate('First Seen') ?>
        </a>
      </th>
      <th style="width: 1%;">
        <a href="javascript:void(0)" onclick="handleSort('last_seen')">
          <?php echo $this->translate('Last Seen') ?>
        </a>
      </th>
      <?php /*
      <th>
        <?php echo $this->translate('Options') ?>
      </th>
       *
       */ ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $this->paginator as $item ): ?>
      <tr>
        <td class="nowrap">
          <?php echo $this->locale()->toNumber($item->node_id) ?>
        </td>
        <td class="nowrap">
          <?php echo $item->signature ?>
        </td>
        <td class="nowrap">
          <?php echo $item->host ?>
        </td>
        <td class="nowrap">
          <?php echo long2ip($item->ip) ?>
        </td>
        <td class="nowrap">
          <?php echo $this->timestamp($item->first_seen) ?>
        </td>
        <td class="nowrap">
          <?php echo $this->timestamp($item->last_seen) ?>
        </td>
        <?php /*
        <td class="admin_table_options">
          <span class="sep">|</span>
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('run'), array('onclick' => 'runTasks(' . $task->task_id . ', $(this));')) ?>
          <span class="sep">|</span>
          <?php echo $this->htmlLink(array('reset' => false, 'action' => 'edit', 'task_id' => $task->task_id), $this->translate('edit')) ?>
          <span class="sep">|</span>
          <?php echo $this->htmlLink(array('reset' => false, 'action' => 'reset-stats', 'task_id' => $task->task_id), $this->translate('reset stats')) ?>

        </td>
         *
         */ ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<br />