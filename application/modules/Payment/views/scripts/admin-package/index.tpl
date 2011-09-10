<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8482 2011-02-16 22:08:42Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2>
  <?php echo $this->translate("Manage Subscription Plans") ?>
</h2>

<p>
  <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_PACKAGES_INDEX_DESCRIPTION") ?>
</p>

<br />


<?php if( !empty($this->error) ): ?>
  <ul class="form-errors">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>

  <br />
<?php /*return; */ endif; ?>


<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Plan'), array(
    'class' => 'buttonlink icon_plan_add',
  )) ?>
</div>

<br />


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
    <?php echo $this->translate(array("%s plan found", "%s plans found", $count), $count) ?>
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
        <?php $class = ( $this->order == 'package_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('package_id', 'DESC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'title' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');">
            <?php echo $this->translate("Title") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'level_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', 'ASC');">
            <?php echo $this->translate("Member Level") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'price' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('price', 'DESC');">
            <?php echo $this->translate("Price") ?>
          </a>
        </th>
        <th style='width: 1%;'>
          <?php echo $this->translate("Billing") ?>
        </th>
        <?php $class = ( $this->order == 'enabled' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('enabled', 'DESC');">
            <?php echo $this->translate("Enabled") ?>
          </a>
        </th>
        <?php $class = ( $this->order == 'signup' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class='admin_table_centered <?php echo $class ?>'>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('signup', 'DESC');">
            <?php echo $this->translate("Signup") ?>
          </a>
        </th>
        <th style='width: 1%;' class='admin_table_centered'>
          <?php echo $this->translate("Active Members") ?>
        </th>
        <th style='width: 1%;' class='admin_table_options'>
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td><?php echo $item->package_id ?></td>
          <td class='admin_table_bold'>
            <?php echo $item->title ?>
          </td>
          <td class='admin_table_centered'>
            <?php if( $item->level_id ): ?>
              <a href='<?php echo $this->url(array('module' => 'authorization','controller' => 'level', 'action' => 'edit', 'id' => $item->level_id)) ?>'>
                <?php echo $this->translate(Engine_Api::_()->getItem('authorization_level', $item->level_id)->getTitle()) ?>
              </a>
            <?php else: ?>
              <em><?php echo $this->translate('Not assigned')?></em>
            <?php endif ?>
          </td>
          <td>
            <?php echo $this->locale()->toNumber($item->price) ?>
          </td>
          <td class="nowrap">
            <?php echo $item->getPackageDescription() ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo ( $item->signup ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->locale()->toNumber(@$this->memberCounts[$item->package_id]) ?>
          </td>
          <td class='admin_table_options'>
            <a href='<?php echo $this->url(array('action' => 'edit', 'package_id' => $item->package_id)) ?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            |
            <a href='<?php echo $this->url(array('controller' => 'subscription', 'action' => 'index', 'package_id' => $item->package_id));?>'>
              <?php echo $this->translate("subscriptions") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>