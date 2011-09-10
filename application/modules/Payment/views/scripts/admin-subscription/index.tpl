<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8263 2011-01-19 01:10:03Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2>
  <?php echo $this->translate("Manage Subscriptions") ?>
</h2>

<p>
  <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_SUBSCRIPTION_INDEX_DESCRIPTION") ?>
</p>

<br />


<?php if( !empty($this->error) ): ?>
  <ul class="form-errors">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>

  <br />
<?php return; endif; ?>


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
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

  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />
<?php endif; ?>



<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s subscription found", "%s subscriptions found", $count), $count) ?>
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
  <table class='admin_table'>
    <thead>
      <tr>
        <?php $class = ( $this->order == 'subscription_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('subscription_id', 'DESC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'user_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'ASC');">
            <?php echo $this->translate("Member") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'package_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('package_id', 'ASC');">
            <?php echo $this->translate("Plan") ?>
          </a>
        </th>
        <th style='width: 1%;' class='admin_table_centered'>
          <?php echo $this->translate("Member Level") ?>
        </th>
        <?php $class = ( $this->order == 'status' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('status', 'ASC');">
            <?php echo $this->translate("Status") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'active' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('active', 'DESC');">
            <?php echo $this->translate("Active") ?>
          </a>
        </th>
        <th style='width: 1%;' class='admin_table_options'>
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item ):
        $user = @$this->users[$item->user_id];
        $package = @$this->packages[$item->package_id];
        $level = @$this->levels[$package->level_id];
        ?>
        <tr>
          <td><?php echo $item->subscription_id ?></td>
          <td class='admin_table_bold'>
            <?php echo ( $user ? $user->__toString() : '<i>' . $this->translate('Deleted Member') . '</i>' ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php if( $package ): ?>
              <a href='<?php echo $this->url(array('module' => 'payment', 'controller' => 'package', 'action' => 'edit', 'package_id' => $package->package_id)) ?>'>
                <?php echo $this->translate($package->title) ?>
              </a>
            <?php else: ?>
              <i><?php echo $this->translate('Missing Plan') ?></i>
            <?php endif ?>
          </td>
          <td class='admin_table_centered'>
            <a href='<?php echo $this->url(array('module' => 'authorization', 'controller' => 'level', 'action' => 'edit', 'id' => $level->level_id)) ?>'>
              <?php echo $this->translate($level->getTitle()) ?>
            </a>
          </td>
          <td><?php echo $this->translate(ucfirst($item->status)) ?></td>
          <td class='admin_table_centered'>
            <?php echo ( $item->active ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_options'>
            <a class="smoothbox" href='<?php echo $this->url(array('action' => 'edit', 'subscription_id' => $item->subscription_id));?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            |
            <a href='<?php echo $this->url(array('action' => 'detail', 'subscription_id' => $item->subscription_id));?>'>
              <?php echo $this->translate("details") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>