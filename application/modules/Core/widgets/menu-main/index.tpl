<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8583 2011-03-10 20:51:31Z jung $
 * @author     John
 */
?>

<?php
  echo $this->navigation()
    ->menu()
    ->setContainer($this->navigation)
    ->setPartial(null)
    ->setUlClass('navigation')
    ->render();
?>
