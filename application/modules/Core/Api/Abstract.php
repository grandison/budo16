<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Api_Abstract
{
  protected $_moduleName;
  
  public function getModuleName()
  {
    if( empty($this->_moduleName) )
    {
      $class = get_class($this);
      if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
        $prefix = $matches[1];
      } else {
        $prefix = $class;
      }
      // @todo sanity
      $this->_moduleName = strtolower($prefix);
    }
    return $this->_moduleName;
  }

  public function api()
  {
    return Engine_Api::_()->setCurrentModule($this->getModuleName());
  }

  public function __call($method, array $arguments = array())
  {
    throw new Engine_Exception(sprintf('Method "%s" not supported', $method));
  }
}