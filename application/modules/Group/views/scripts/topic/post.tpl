<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: post.tpl 7244 2010-09-01 01:49:53Z john $
 * @author	   John
 */
?>

<h2>
  <?php echo $this->group->__toString() ?>
  <?php echo $this->translate('&#187; Discussions');?>
</h2>

<h3>
  <?php echo $this->topic->__toString() ?>
</h3>

<br />

<?php if( $this->message ) echo $this->message ?>

<?php if( $this->form ) echo $this->form->render($this) ?>