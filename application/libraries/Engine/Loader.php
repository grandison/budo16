<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Loader
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Loader.php 7244 2010-09-01 01:49:53Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Loader
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Loader
{
  /**
   * Singleton instance
   * 
   * @var Engine_Loader
   */
  static protected $_instance;

  /**
   * Class prefix to path mappings
   * 
   * @var array
   */
  protected $_prefixToPaths = array();

  /**
   * Array of loaded resources by class name
   * 
   * @var array
   */
  protected $_components = array();

  /**
   * Get current singleton instance
   * 
   * @return Engine_Loader
   */
  public static function getInstance()
  {
    if( null === self::$_instance )
    {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  /**
   * Set current loader instance
   * 
   * @param Engine_Loader $loader
   */
  public static function setInstance(Engine_Loader $loader = null)
  {
    self::$_instance = $loader;
  }

  /**
   * Constructor
   */
  public function __construct()
  {
    spl_autoload_register(array(__CLASS__, 'autoload'));
  }

  /**
   * Registered in {@link Engine_Loader::__construct()} to spl_autoload_register
   * 
   * @param string $class
   * @return boolean
   */
  static public function autoload($class)
  {
    $self = self::getInstance();
    $segments = explode('_', $class);
    $prefix = array_shift($segments);

    // Whoops we only had one segment?
    if( count($segments) == 0 )
    {
      return false;
    }

    // First segment doesn't exist in path
    /*
    if( !array_key_exists($prefix, $self->_prefixToPaths) )
    {
      $path = null;
    }
    else
    {
      $path = $self->_prefixToPaths[$prefix];
      if( !$path )
      {
        $path = null;
      }
    }
     */
    if( !empty($self->_prefixToPaths[$prefix]) ) {
      $path = $self->_prefixToPaths[$prefix];
    } else {
      array_unshift($segments, $prefix);
    }

    settype($path, "string");

    // This will use the include path if no path is registered
    if( $path ) $path .= DIRECTORY_SEPARATOR;
    $path .= join(DIRECTORY_SEPARATOR, $segments) . '.php';

    $includeResult = include_once $path;

    return $includeResult;
  }

  /**
   * Registers a class prefix to path mapping
   * 
   * @param string $prefix
   * @param string $path
   * @return Engine_Loader
   */
  public function register($prefix, $path = null)
  {
    $this->_prefixToPaths[$prefix] = $path;
    return $this;
  }

  /**
   * Force load a class
   * 
   * @param string $class
   * @throws Engine_Loader_Exception If unable to load
   */
  public static function loadClass($class)
  {
    if( !class_exists($class, false) )
    {
      if( !self::autoload($class) )
      {
        throw new Engine_Loader_Exception(sprintf('Could not load class: %s', $class));
      }
    }
  }

  /**
   * Same as {@link Engine_Loader::loadClass()} except returns status
   * 
   * @param string $class
   * @return boolean
   */
  public static function conditionalLoadClass($class)
  {
    return (bool) self::autoload($class);
  }

  /**
   * Loads and instantiates a resource class
   * 
   * @param string $class
   * @return mixed
   */
  public function load($class)
  {
    if( isset($this->_components[$class]) )
    {
      return $this->_components[$class];
    }

    if( !class_exists($class, false) )
    {
      self::loadClass($class);
    }

    return $this->_components[$class] = new $class();
  }
}

