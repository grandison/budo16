<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Modules.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Modules extends Engine_Db_Table
{
  protected $_modules;

  protected $_modulesAssoc = array();

  protected $_enabledModuleNames;

  public function getModule($name)
  {
    if( null === $this->_modules ) {
      $this->getModules();
    }

    if( !empty($this->_modulesAssoc[$name]) ) {
      return $this->_modulesAssoc[$name];
    }

    return null;
  }
  
  public function getModules()
  {
    if( null === $this->_modules ) {
      $this->_modules = $this->fetchAll();
      foreach( $this->_modules as $module ) {
        $this->_modulesAssoc[$module->name] = $module;
      }
    }

    return $this->_modules;
  }

  public function getModulesAssoc()
  {
    if( null === $this->_modules ) {
      $this->getModules();
    }
    
    return $this->_modulesAssoc;
  }

  public function hasModule($name)
  {
    return !empty($this->_modulesAssoc[$name]);
  }

  public function isModuleEnabled($name)
  {
    if( empty($this->_modulesAssoc[$name]) || empty($this->_modulesAssoc[$name]->enabled) ) {
      return false;
    }

    return true;
  }

  public function getEnabledModuleNames()
  {
    if( null === $this->_enabledModuleNames ) {
      foreach( $this->getModules() as $module ) {
        if( $module->enabled ) {
          $this->_enabledModuleNames[] = $module->name;
        }
      }
    }

    return $this->_enabledModuleNames;
  }
}
