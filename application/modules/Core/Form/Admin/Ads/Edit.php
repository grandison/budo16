<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Ads_Edit extends Core_Form_Admin_Ads_Create
{
  public function init()
  {
    parent::init();
    $this->setTitle('Edit Advertising Campaign');
    $this->setDescription('Follow this guide to design and launch a new advertising campaign.');

    $this->submit->setLabel('Save Changes');
  }
}