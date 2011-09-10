<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Refresh.php 7641 2010-10-14 22:51:27Z steve $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager_Operation_Refresh extends Engine_Package_Manager_Operation_Abstract
{
  protected function _setPackages(Engine_Package_Manifest $targetPackage,
      Engine_Package_Manifest $currentPackage = null)
  {
    $this->_targetPackage  = $targetPackage;
    $this->_currentPackage = null;//$currentPackage;
  }
  
  public function doInstall()
  {

  }
}