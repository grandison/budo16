<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: upload.tpl 7935 2010-12-05 17:07:09Z char $
 * @author	   John
 */
?>

<h2>
    <?php echo $this->group->__toString() ?>
    <?php echo $this->translate('&#187; Photos');?>
</h2>

<?php echo $this->form->render($this) ?>