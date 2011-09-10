<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: info.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<div id='profile_stats'>
  <h4><?php echo $this->translate('Member Info');?></h4>
  <ul>
    <?php if( !empty($this->memberType) ): ?>
    <li>
      <?php echo $this->translate('Member Type:');?> <a href="#"><?php echo $this->memberType ?></a>
    </li>
    <?php endif; ?>
    <?php /*
    <li>
      Networks: Los Angeles, Webligo
    </li> */ ?>
    <li>
      <?php echo $this->translate(array("Profile Views: %s view","Profile Views: %s views",$this->subject()->view_count), $this->locale()->toNumber($this->subject()->view_count)) ?>
    </li>
    <li>
      <?php echo $this->translate(array("Friends: %s friend","Friends: %s friends",$this->subject()->member_count), $this->locale()->toNumber($this->subject()->member_count)) ?>
    </li>
    <li>
      <?php echo $this->translate("Last Update: %s", $this->timestamp($this->subject()->modified_date)) ?>
    </li>
    <li>
      <?php echo $this->translate("Joined: %s", $this->timestamp($this->subject()->creation_date)) ?>
    </li>
  </ul>
</div>