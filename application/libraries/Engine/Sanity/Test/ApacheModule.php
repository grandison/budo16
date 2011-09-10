<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ApacheModule.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_ApacheModule extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'notApache' => 'Unable to check.',
    'noModule' => 'The module %module% appears to not be installed on your system.',
  );

  protected $_messageVariables = array(
    'module' => '_module',
  );

  protected $_module;

  public function setModule($module)
  {
    $this->_module = $module;
    return $this;
  }

  public function getModule()
  {
    return $this->_module;
  }

  public function execute()
  {
    // Can't check
    if( !function_exists('apache_get_modules') || !is_callable('apache_get_modules') ) {
      return $this->_error('notApache');
    }

    // Check
    $module = $this->getModule();
    $modules = apache_get_modules();
    if( !empty($module) ) {
      if( !in_array($module, $modules) ) {
        return $this->_error('noModule');
      }
    }
  }
}