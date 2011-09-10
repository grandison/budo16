<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: upload.tpl 8113 2010-12-23 00:01:53Z char $
 * @author     Sami
 */
?>

<h2>
    <?php echo $this->event->__toString() ?>
    <?php echo $this->translate('&#187; Photos');?>
</h2>

<?php echo $this->form->render($this) ?>