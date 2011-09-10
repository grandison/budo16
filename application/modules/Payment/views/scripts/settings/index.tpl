<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('My Settings');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<?php if( $this->isAdmin ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Subscriptions are not required for ' .
          'administrators and moderators.') ?>
    </span>
  </div>
<?php return; endif; ?>

<form method="get" action="<?php echo $this->escape($this->url(array('action' => 'confirm'))) ?>"
      class="global_form payment_form_settings" enctype="application/x-www-form-urlencoded">
  <div>
    <div>
      <h3>
        <?php echo $this->translate('Subscription') ?>
      </h3>
      <?php if( $this->currentPackage && $this->currentSubscription ): ?>
        <p class="form-description">
          <?php echo $this->translate('The plan you are currently subscribed ' .
              'to is: %1$s', '<strong>' .
              $this->translate($this->currentPackage->title) . '</strong>') ?>
          <br />
          <?php echo $this->translate('You are currently paying: %1$s',
              '<strong>' . $this->currentPackage->getPackageDescription()
              . '</strong>') ?>
        </p>
        <p style="padding-top: 15px; padding-bottom: 15px;">
          <?php echo $this->translate('If you would like to change your ' .
              'subscription, please select an option below.') ?>
        </p>
      <?php else: ?>
        <p class="form-description">
          <?php echo $this->translate('You have not yet selected a ' .
              'subscription plan. Please choose one now below.') ?>
        </p>
      <?php endif; ?>
      <div class="form-elements">
        <?php $count = 0; ?>
        <?php foreach( $this->packages as $package ):
          $id = $package->package_id;
          $attribs = array('id' => 'package-' . $id, 'class' => 'package-select');
          if( $id == $this->currentPackage->package_id ) {
            continue;
            //$attribs['disabled'] = 'disabled';
          }
          $count++;
          ?>
          <div id="package-<?php echo $id ?>-wrapper" class="form-wrapper">
            <div id="package-<?php echo $id ?>-element" class="form-element">
              <?php echo $this->formSingleRadio('package_id', $package->package_id, $attribs) ?>
              <div class="package-container">
                <label class="package-label" for="package-<?php echo $id ?>">
                  <?php echo $this->translate($package->title) ?>
                  <?php echo $this->translate('(%1$s)', $package->getPackageDescription()) ?>
                </label>
                <p class="package-description">
                  <?php echo $this->translate($package->description) ?>
                </p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if( $count > 0 ): ?>
          <div id="execute-wrapper" class="form-wrapper">
            <div id="execute-element" class="form-element">
              <button type="submit" name="execute" onclick="var found = false; $$('input.package-select').each(function(el){ if( el.get('checked') ) { found = true; } }); return found; ">
                <?php echo $this->translate('Change Plan') ?>
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>
