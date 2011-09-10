<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Website.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Form_Element_Website extends Engine_Form_Element_Text
{
  public function init()
  {
    $this->addFilter('PregReplace', array('/\s*[a-zA-Z0-9]{2,5}:\/\//', ''));
    //$this->addValidator('Hostname', true);
  }
}