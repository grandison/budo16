<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: raw-order-detail.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2 class="payment_transaction_detail_headline">
  <?php echo $this->translate("Raw Order Details") ?>
</h2>

<?php if( !is_array($this->data) ): ?>

  <div class="error">
    <span>
      Order could not be found.
    </span>
  </div>

<?php else: ?>

  <dl class="payment_transaction_details">
    <?php foreach( $this->data as $key => $value ): ?>
      <dd>
        <?php echo $key ?>
      </dd>
      <dt>
        <?php echo $value ?>
      </dt>
    <?php endforeach; ?>
  </dl>

<?php endif; ?>