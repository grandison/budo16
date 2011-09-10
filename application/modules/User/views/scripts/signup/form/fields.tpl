<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: fields.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array(
    'topLevelId' => $this->form->getTopLevelId(),
    'topLevelValue' => $this->form->getTopLevelValue(),
  ));
?>

<?php echo $this->form->render($this) ?>
