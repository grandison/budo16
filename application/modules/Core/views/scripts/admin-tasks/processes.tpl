<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: processes.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate("Process List") ?></h2>

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
    'CORE_VIEWS_SCRIPTS_ADMINTASKS_PROCESSES_DESCRIPTION' !== ($desc = $this->translate("CORE_VIEWS_SCRIPTS_ADMINTASKS_PROCESSES_DESCRIPTION")) ?
    $desc : '' ) ?>
</p>

<br />




<div class="admin_table_form">
  <form id="admin-tasks-form" method="post" action="<?php echo $this->url() ?>">

    <table class="admin_table">
      <thead>
        <tr>
          <th style="width: 1%;">
            <input type="checkbox" onclick="$$('input[type=checkbox][name]').set('checked', this.get('checked'));" />
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('pid')">
              <?php echo $this->translate('PID') ?>
            </a>
          </th>
          <th>
            <a href="javascript:void(0)" onclick="handleSort('parent_pid')">
              <?php echo $this->translate('Parent PID') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('system_pid')">
              <?php echo $this->translate('System PID') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('started')">
              <?php echo $this->translate('Started') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('timeout')">
              <?php echo $this->translate('Timeout') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <?php echo $this->translate('Name') ?>
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
        <?php foreach( $this->processes as $process ): ?>
          <tr>
            <td>
              <input type="checkbox" name="selection[]" value="<?php echo $process->pid ?>" />
            </td>
            <td>
              <?php echo $this->locale()->toNumber($process->pid) ?>
            </td>
            <td>
              <?php echo $this->locale()->toNumber($process->parent_pid) ?>
            </td>
            <td>
              <?php echo $this->locale()->toNumber($process->system_pid) ?>
            </td>
            <td>
              <?php echo $this->locale()->toDateTime($process->started) ?>
            </td>
            <td>
              <?php echo $this->locale()->toNumber($process->timeout) ?>
            </td>
            <td>
              <?php echo $process->name ?>
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
    
  </form>
</div>