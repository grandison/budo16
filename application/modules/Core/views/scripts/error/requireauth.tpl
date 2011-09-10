<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: requireauth.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate('Private Page') ?></h2>

<p>
  <?php echo $this->translate('You do not have permission to view this private page.') ?>
</p>

<br />

<a class='buttonlink icon_back' href='javascript:void(0);' onClick='history.go(-1);'>
  <?php echo $this->translate('Go Back') ?>
</a>