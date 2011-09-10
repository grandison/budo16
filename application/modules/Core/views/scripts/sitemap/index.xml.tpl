<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.xml.tpl 8791 2011-04-05 22:56:38Z john $
 * @author     John
 */
?>
<?php // Note: there cannot be a space above this line
  echo $this->navigation()
    ->sitemap()
    ->setContainer($this->navigation)
    ->setFormatOutput(true)
    ->render();
?>