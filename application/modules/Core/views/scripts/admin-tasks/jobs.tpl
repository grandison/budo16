<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: jobs.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate("Job Queue") ?></h2>

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
    'CORE_VIEWS_SCRIPTS_ADMINTASKS_JOBS_DESCRIPTION' !== ($desc = $this->translate("CORE_VIEWS_SCRIPTS_ADMINTASKS_JOBS_DESCRIPTION")) ?
    $desc : '' ); ?>
</p>

<br />


<?php if( $this->paginator->getTotalItemCount() > 0 || !empty($this->filterValues) ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />


  <script type="text/javascript">
    var currentOrder = '<?php echo $this->filterValues['order'] ?>';
    var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        $('direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        $('order').value = order;
        $('direction').value = default_direction;
      }
      $('filter_form').submit();
    }
  </script>
<?php endif; ?>


<?php /*
<div>
  <?php echo $this->htmlLink(array('action' => 'job-add', 'reset' => false), $this->translate('Add Job'), array(
    'class' => 'buttonlink',
    'style' => 'background-image: url(application/modules/Network/externals/images/admin/add.png);'
  )) ?>
</div>

<br />
*/ ?>



<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s job found", "%s jobs found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>

<br />


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="admin_table_form">
    <form id="admin-tasks-form" method="post" action="<?php echo $this->url() ?>">

      <table class="admin_table">
        <thead>
          <tr>
            <?php /*
            <?php $class = ( $this->order == 'subscription_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
            <th style="width: 1%;">
              <input type="checkbox" onclick="$$('input[type=checkbox][name]').set('checked', this.get('checked'));" />
            </th>
             */ ?>
            <?php $class = ( $this->order == 'job_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
            <th style="width: 1%;" class="<?php echo $class ?>">
              <a href="javascript:void(0)" onclick="changeOrder('job_id', 'DESC')">
                <?php echo $this->translate('ID') ?>
              </a>
            </th>
            <?php $class = ( $this->order == 'jobtype_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
            <th class="<?php echo $class ?>">
              <a href="javascript:void(0)" onclick="changeOrder('jobtype_id', 'ASC')">
                <?php echo $this->translate('Name') ?>
              </a>
            </th>
            <?php $class = ( $this->order == 'state' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
            <th style="width: 1%;" class="<?php echo $class ?>">
              <a href="javascript:void(0)" onclick="changeOrder('state', 'ASC')">
                <?php echo $this->translate('State') ?>
              </a>
            </th>
            <?php $class = ( $this->order == 'progress' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
            <th style="width: 1%;" class="<?php echo $class ?>">
              <a href="javascript:void(0)" onclick="changeOrder('progress', 'DESC')">
                <?php echo $this->translate('Progress') ?>
              </a>
            </th>
            <th style="width: 1%;">
              <?php $class = ( $this->order == 'creation_date' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
              <a href="javascript:void(0)" onclick="changeOrder('creation_date', 'DESC')" class="<?php echo $class ?>">
                <?php echo $this->translate('Queued') ?>
              </a>
              /
              <?php $class = ( $this->order == 'started_date' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
              <a href="javascript:void(0)" onclick="changeOrder('started_date', 'DESC')" class="<?php echo $class ?>">
                <?php echo $this->translate('Started') ?>
              </a>
              /
              <?php $class = ( $this->order == 'completion_date' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
              <a href="javascript:void(0)" onclick="changeOrder('completion_date', 'DESC')" class="<?php echo $class ?>">
                <?php echo $this->translate('Completed') ?>
              </a>
            </th>
            <th>
              <?php echo $this->translate('Options') ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $this->paginator as $job ):
            $jobtype = $this->jobtypes[$job->jobtype_id];
            ?>
            <tr>
              <?php /*
              <td>
                <input type="checkbox" name="selection[]" value="<?php echo $job->job_id ?>" />
              </td>
               */ ?>
              <td>
                <?php echo $this->locale()->toNumber($job->job_id) ?>
              </td>
              <td>
                <?php if( empty($jobtype) ): ?>
                  Missing type (<?php echo $job->jobtype_id ?>)
                <?php elseif( !empty($jobtype->title) ): ?>
                  <?php echo $jobtype->title ?>
                <?php else: ?>
                  <?php echo $jobtype->plugin ?>
                <?php endif; ?>
              </td>
              <td>
                <?php echo $this->translate(ucwords($job->state)) ?>
              </td>
              <td>
                <?php echo $this->locale()->toNumber($job->progress * 100) ?>%
              </td>
              <td>
                <?php echo $this->locale()->toDateTime($job->creation_date) ?>
                <br />
                <?php echo $this->locale()->toDateTime($job->started_date) ?>
                <br />
                <?php echo $this->locale()->toDateTime($job->completion_date) ?>
              </td>
              <td class="admin_table_options">
                <?php /*
                <span class="sep">|</span>
                <?php echo $this->htmlLink(array('reset' => false, 'action' => 'edit', 'task_id' => $job->job_id), $this->translate('edit')) ?>
                */ ?>
                <?php if( in_array($job->state, array('active', 'sleeping', 'pending')) ): ?>
                  <span class="sep">|</span>
                  <?php echo $this->htmlLink(array('reset' => false, 'action' => 'job-cancel', 'job_id' => $job->job_id), $this->translate('cancel')) ?>
                <?php endif; ?>
                <?php if( in_array($job->state, array('failed', 'cancelled')) ): ?>
                  <span class="sep">|</span>
                  <?php echo $this->htmlLink(array('reset' => false, 'action' => 'job-retry', 'job_id' => $job->job_id), $this->translate('restart')) ?>
                <?php endif; ?>
                <?php if( !empty($job->messages) ): ?>
                  <span class="sep">|</span>
                  <?php echo $this->htmlLink(array('reset' => false, 'action' => 'job-messages', 'job_id' => $job->job_id), $this->translate('messages'), array('class' => 'smoothbox')) ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <br />

    </form>
  </div>
<?php endif; ?>
