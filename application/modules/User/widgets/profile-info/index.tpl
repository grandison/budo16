<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7250 2010-09-01 07:42:35Z john $
 * @author     John
 */
?>

<ul>
  <?php if( !empty($this->memberType) ): ?>
  <li>
    <?php echo $this->translate('Member Type:') ?>
    <?php // @todo implement link ?>
    <?php echo $this->translate($this->memberType) ?>
  </li>
  <?php endif; ?>
  <?php if( !empty($this->networks) && count($this->networks) > 0 ): ?>
  <li>
    <?php echo $this->translate('Networks:') ?>
    <?php echo $this->fluentList($this->networks) ?>
  </li>
  <?php endif; ?>
  <li>
    <?php echo $this->translate('Profile Views:') ?>
    <?php echo $this->translate(array('%s view', '%s views', $this->subject()->view_count),$this->locale()->toNumber($this->subject()->view_count)) ?>
  </li>
  <li>
    <?php echo $this->translate('Friends:') ?>
    <?php echo $this->translate(array('%s friend', '%s friends', $this->subject()->member_count),$this->locale()->toNumber($this->subject()->member_count)) ?>
  </li>
  <li>
    <?php echo $this->translate('Last Update:'); ?>
    <?php echo $this->timestamp($this->subject()->modified_date) ?>
  </li>
  <li>
    <?php echo $this->translate('Joined:') ?>
    <?php echo $this->timestamp($this->subject()->creation_date) ?>
  </li>
</ul>