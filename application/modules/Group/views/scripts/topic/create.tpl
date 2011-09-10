<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: create.tpl 7244 2010-09-01 01:49:53Z john $
 * @author	   John
 */
?>

<h2>
  <?php echo $this->group->__toString() ?>
  <?php echo $this->translate('&#187; Discussions');?>
</h2>

<?php echo $this->form->render($this) ?>