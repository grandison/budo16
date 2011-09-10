<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>


<div class="admin_home_wrapper">

  <div class="admin_home_right">
    <?php echo $this->content()->renderWidget('core.admin-statistics') ?>
    <?php echo $this->content()->renderWidget('core.admin-environment') ?>
  </div>

  <div class="admin_home_middle">
    <?php echo $this->content()->renderWidget('core.admin-dashboard') ?>
    <?php echo $this->content()->renderWidget('core.admin-news') ?>
  </div>

</div>
