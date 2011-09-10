<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: tabs.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php // This is rendered by application/modules/core/views/scripts/_navJsTabs.tpl
  echo $this->navigation()
    ->menu()
    ->setContainer($this->navigation)
    ->setPartial(array('_navJsTabs.tpl', 'core'))
    ->render()
?>
