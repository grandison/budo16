<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8225 2011-01-15 02:58:49Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2>
  <?php echo $this->translate("Transactions") ?>
</h2>


<p>
  <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_INDEX_INDEX_DESCRIPTION") ?>
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
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />
<?php endif; ?>


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


<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s transaction found", "%s transactions found", $count), $count) ?>
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
        <?php $class = ( $this->order == 'transaction_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('transaction_id', 'DESC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'user_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'ASC');">
            <?php echo $this->translate("Member") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'gateway_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('gateway_id', 'ASC');">
            <?php echo $this->translate("Gateway") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'type' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('type', 'DESC');">
            <?php echo $this->translate("Type") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'state' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('state', 'DESC');">
            <?php echo $this->translate("State") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'amount' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('amount', 'DESC');">
            <?php echo $this->translate("Amount") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'timestamp' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('timestamp', 'DESC');">
            <?php echo $this->translate("Date") ?>
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
        $order = @$this->orders[$item->order_id];
        $gateway = @$this->gateways[$item->gateway_id];
        ?>
        <tr>
          <td><?php echo $item->transaction_id ?></td>
          <td class='admin_table_bold'>
            <?php echo ( $user ? $user->__toString() : '<i>' . $this->translate('Deleted or Unknown Member') . '</i>' ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo ( $gateway ? $gateway->title : '<i>' . $this->translate('Unknown Gateway') . '</i>' ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->translate(ucfirst($item->type)) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->translate(ucfirst($item->state)) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->locale()->toCurrency($item->amount, $item->currency) ?>
            <?php echo $this->translate('(%s)', $item->currency) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->locale()->toDateTime($item->timestamp) ?>
          </td>
          <td class='admin_table_options'>
            <a class="smoothbox" href='<?php echo $this->url(array('action' => 'detail', 'transaction_id' => $item->transaction_id));?>'>
              <?php echo $this->translate("details") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>