<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: gateway.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<?php if( $this->status == 'pending' ): // Check for pending status ?>
  Your subscription is pending payment. You will receive an email when the
  payment completes.
<?php else: ?>

  <form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>"
        class="global_form" enctype="application/x-www-form-urlencoded">
    <div>
      <div>
        <h3>
          <?php echo $this->translate('Pay for Access') ?>
        </h3>
        <p class="form-description">
          <?php echo $this->translate('You have selected an account type that requires ' .
            'recurring subscription payments. You will be taken to a secure ' .
            'checkout area where you can setup your subscription. Remember to ' .
            'continue back to our site after your purchase to sign in to your ' .
            'account.') ?>
        </p>
        <p style="font-weight: bold; padding-top: 15px; padding-bottom: 15px;">
          <?php if( $this->package->recurrence ): ?>
            <?php echo $this->translate('Please setup your subscription to continue:') ?>
          <?php else: ?>
            <?php echo $this->translate('Please pay a one-time fee to continue:') ?>
          <?php endif; ?>
          <?php echo $this->package->getPackageDescription() ?>
        </p>
        <div class="form-elements">
          <div id="buttons-wrapper" class="form-wrapper">
              <?php foreach( $this->gateways as $gatewayInfo ):
                $gateway = $gatewayInfo['gateway'];
                $plugin = $gatewayInfo['plugin'];
                $first = ( !isset($first) ? true : false );
                ?>
                <?php if( !$first ): ?>
                  <?php echo $this->translate('or') ?>
                <?php endif; ?>
                <button type="submit" name="execute" onclick="$('gateway_id').set('value', '<?php echo $gateway->gateway_id ?>')">
                  <?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?>
                </button>
              <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <input type="hidden" name="gateway_id" id="gateway_id" value="" />
  </form>

<?php endif; ?>
