<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: finish.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<form method="get" action="<?php echo $this->escape($this->url(array(), 'default', true)) ?>"
      class="global_form" enctype="application/x-www-form-urlencoded">
  <div>
    <div>

      <?php if( $this->status == 'pending' ): ?>

        <h3>
          <?php echo $this->translate('Payment Pending') ?>
        </h3>
        <p class="form-description">
          <?php echo $this->translate('Thank you for submitting your ' .
              'payment. Your payment is currently pending - your account ' .
              'will be activated when we are notified that the payment has ' .
              'completed successfully. Please return to our login page ' .
              'when you receive an email notifying you that the payment ' .
              'has completed.') ?>
        </p>
        <div class="form-elements">
          <div id="buttons-wrapper" class="form-wrapper">
            <button type="submit">
              <?php echo $this->translate('Back to Home') ?>
            </button>
          </div>
        </div>

      <?php elseif( $this->status == 'active' ): ?>

        <h3>
          <?php echo $this->translate('Payment Complete') ?>
        </h3>
        <p class="form-description">
          <?php echo $this->translate('Thank you! Your payment has ' .
              'completed successfully.') ?>
        </p>
        <div class="form-elements">
          <div id="buttons-wrapper" class="form-wrapper">
            <button type="submit">
              <?php echo $this->translate('Continue') ?>
            </button>
          </div>
        </div>

      <?php else: //if( $this->status == 'failed' ): ?>

        <h3>
          <?php echo $this->translate('Payment Failed') ?>
        </h3>
        <p class="form-description">
          <?php if( empty($this->error) ): ?>
            <?php echo $this->translate('Our payment processor has notified ' .
                'us that your payment could not be completed successfully. ' .
                'We suggest that you try again with another credit card ' .
                'or funding source.') ?>
            <?php else: ?>
              <?php echo $this->translate($this->error) ?>
            <?php endif; ?>
        </p>
        <div class="form-elements">
          <div id="buttons-wrapper" class="form-wrapper">
            <button type="submit">
              <?php echo $this->translate('Back to Home') ?>
            </button>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>
</form>
