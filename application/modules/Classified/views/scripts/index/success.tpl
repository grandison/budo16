<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: success.tpl 7374 2010-09-14 05:02:38Z john $
 * @author     Jung
 */
?>

<div class='global_form'>
  <form method="post" class="global_form">
    <div>
      <div>
      <h3><?php echo $this->translate('Listing Posted');?></h3>
      <p>
        <?php echo $this->translate('Your listing was successfully published. Would you like to add some photos to it?');?>
      </p>
      <br />
      <p>
        <input type="hidden" name="confirm" value="true"/>
        <button type='submit'><?php echo $this->translate('Add Photos');?></button>
        <?php echo $this->translate('or');?>
        <a href='<?php echo $this->url(array('action' => 'manage'), 'classified_general', true) ?>'>
          <?php echo $this->translate('continue to my listing');?>
        </a>
      </p>
    </div>
    </div>
  </form>
</div>