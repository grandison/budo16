<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7299 2010-09-06 06:08:27Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Authorization_Api_Core extends Core_Api_Abstract
{
  /**
   * Constants
   */
  const LEVEL_DISALLOW = 0;
  const LEVEL_ALLOW = 1;
  const LEVEL_MODERATE = 2;
  const LEVEL_NONBOOLEAN = 3;
  const LEVEL_IGNORE = 4;
  const LEVEL_SERIALIZED = 5;

  /**
   * @var array an array of registered adapters
   */
  protected $_adapters = array();

  /**
   * @var array Adapter names by order
   */
  protected $_order = array();

  /**
   * @var bool Need to sort adapters?
   */
  protected $_needsSort = false;

  static protected $_constants = array(
    0 => 'disallow',
    1 => 'allow',
    2 => 'moderate',
    3 => 'nonboolean',
    4 => 'ignore',
    5 => 'serialized',
  );



  // General

  static public function getConstantKey($constantValue)
  {
    if( is_scalar($constantValue) && isset(self::$_constants[$constantValue]) ) {
      return self::$_constants[$constantValue];
    }

    return null;
  }


  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->loadDefaultAdapters();
  }

  /**
   * Magic getter (gets an adapter)
   *
   * @param string $key The adapter type
   * @return Authorization_Model_Adapter_Abstract
   */
  public function __get($key)
  {
    return $this->getAdapter($key);
  }

  /**
   * Gets the specified permission for the context
   *
   * @param Core_Model_Item_Abstract|string $resource The resource type or object that is being accessed
   * @param Core_Model_Item_Abstract $role The item (user) performing the action
   * @param string $action The name of the action being performed
   * @return mixed 0/1 for allowed, or data for settings
   */
  public function isAllowed($resource, $role, $action = 'view')
  {
    if( null === $resource )
    {
      $resource = Engine_Api::_()->core()->getSubject();
    }
    
    if( null === $role ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( null !== $viewer && $viewer->getIdentity() ) {
        $role = $viewer;
      } else {
        $role = 'everyone';
      }
    }
    
    // Allow resource to specify an object that it inherits permissions from
    if( is_object($resource) )
    {
      $resource = $resource->getAuthorizationItem();
    }

    if( !is_string($action) )
    {
      throw new Authorization_Model_Exception('action must be a string');
    }
    
    // Iterate over each adapter and check permission
    $final = self::LEVEL_DISALLOW;
    foreach( $this->getAdapters() as $adapter )
    {

      $result = $adapter->isAllowed($resource, $role, $action);

      switch( $result ) {
        // Unknown value, ignore, nonboolean
        default:
        case self::LEVEL_IGNORE:
        case self::LEVEL_NONBOOLEAN:
        case self::LEVEL_SERIALIZED:
          continue;
          break;
        case self::LEVEL_DISALLOW:
          return self::LEVEL_DISALLOW;
          break;
        case self::LEVEL_MODERATE:
          return self::LEVEL_ALLOW;
          break;
        case self::LEVEL_ALLOW:
          $final = self::LEVEL_ALLOW;
          break;
      }
    }

    return $final;
  }



  // Adapters

  /**
   * Adds an authorization adapter to the stack
   *
   * @param Authorization_Model_Adapter_Abstract $adapter The authorization adapter
   * @param int $order The order for execution
   * @return Authorization_Model_Api
   */
  public function addAdapter(Authorization_Model_AdapterInterface $adapter)
  {
    $name = $adapter->getAdapterName();
    $this->_adapters[$name] = $adapter;
    $this->_order[$name] = $adapter->getAdapterPriority();
    $this->_needsSort = true;
    return $this;
  }

  /**
   * Clears the current adapters
   *
   * @return Authorization_Model_Api
   */
  public function clearAdapters()
  {
    $this->_adapters = array();
    $this->_order = array();
    return $this;
  }

  /**
   * Gets an adapter by class name
   *
   * @param string $type The type of the adapter
   * @return Authorization_Model_Adapter_Abstract|null
   */
  public function getAdapter($type)
  {
    return $this->_adapters[$type];
  }

  public function getAdapters()
  {
    $this->_sort();

    $adapters = array();
    foreach( $this->_order as $type => $order ) {
      $adapters[] = $this->_adapters[$type];
    }

    return $adapters;
  }

  /**
   * Set the order of an adapter
   *
   * @param string $name The name of the adapter
   * @param int $order The order to set
   * @return Authorization_Model_Api
   */
  public function setAdapterOrder($name, $order = 100)
  {
    if( isset($this->_adapters[$name]) )
    {
      $this->_order[$name] = $order;
    }

    return $this;
  }

  /**
   * Removes an adapter by class name
   *
   * @param string $name The name of the adapter
   * @return Authorization_Model_Api
   */
  public function removeAdapter($name)
  {
    if( $name instanceof Authorization_Model_AdapterInterface )
    {
      $name = $type->getAdapterName();
    }

    if( is_string($name) )
    {
      unset($this->_adapters[$name]);
      unset($this->_order[$name]);
      $this->_needsSort = true;
    }

    return $this;
  }

  /**
   * Loads the default adapters
   *
   * @return Authorization_Model_Api
   */
  public function loadDefaultAdapters()
  {
    if( empty($this->_adapters) )
    {
      $this->addAdapter(Engine_Api::_()->getDbtable('permissions', 'authorization'), 150)
        ->addAdapter(Engine_Api::_()->getDbtable('allow', 'authorization'), 50);
    }

    return $this;
  }
  
  protected function _sort()
  {
    if( $this->_needsSort )
    {
      arsort($this->_order);
      $this->_needsSort = false;
    }
  }



  // permissions functions

  public function getPermission($level_id, $type, $name)
  {
    $permissionTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $select = $permissionTable->select()
      ->where('level_id = ?', $level_id)
      ->where('type = ?', $type)
      ->where('name = ?', $name)
      ;

    $level_permission = $permissionTable->fetchRow($select);
    if( !$level_permission ) {
      return self::LEVEL_DISALLOW;
    } else if( !empty($level_permission->params) ) {
      return $level_permission->params;
    } else {
      return $level_permission->value;
    }
  }

}