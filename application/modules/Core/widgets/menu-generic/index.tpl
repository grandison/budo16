<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8810 2011-04-07 18:24:20Z jung $
 * @author     John
 */
?>

<?php
  echo $this->navigation()
    ->menu()
    ->setContainer($this->navigation)
    ->setPartial(null)
    ->setUlClass($this->ulClass)
    ->render();
?>
