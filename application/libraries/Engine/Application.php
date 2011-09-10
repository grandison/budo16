<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Application.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Application
{
  // Properties

  /**
   * The environment. Used to flag certain debug features on or off.
   * 
   * @var string
   */
  protected $_environment;

  /**
   * Contains the loader/autoloader instance
   * 
   * @var Engine_Loader
   */
  protected $_autoloader;

  /**
   * Contains the primary bootstrap object
   * 
   * @var Engine_Application_Bootstrap_Abstract
   */
  protected $_bootstrap;

  /**
   * Misc options
   * 
   * @var array
   */
  protected $_options = array();


  
  // General

  /**
   * Constructor
   * 
   * @param string $environment The environment (development/production)
   * @param array|Zend_Config $options The options to set
   */
  public function __construct($environment, $options)
  {
    $this->_environment = $environment;
    
    if( is_object($options) && method_exists($options, 'toArray') )
    {
      $options = $options->toArray();
    }

    if( is_array($options) )
    {
      $this->setOptions($options);
    }

    if( !$this->getOption('noStripGlobals', false) )
    {
      self::_stripGlobals();
    }
  }

  /**
   * Bootstrap the application
   * 
   * @return Engine_Application
   */
  public function bootstrap($name = null)
  {
    $this->getBootstrap()->bootstrap($name = null);
    return $this;
  }

  /**
   * Run the application
   * 
   * @return Engine_Application
   */
  public function run()
  {
    $this->getBootstrap()->run();
    return $this;
  }

  /**
   * Set options
   * 
   * @param array $options The options to set
   */
  public function setOptions(array $options)
  {
    foreach( $options as $key => $value )
    {
      $method = 'set'.ucfirst($key);
      if( method_exists($this, $method) )
      {
        $this->$method($value);
      }
      else
      {
        $this->setOption($key, $value);
      }
    }
  }

  /**
   * Get the loader object
   * 
   * @return Engine_Loader
   */
  public function getAutoloader()
  {
    if( null === $this->_autoloader )
    {
      $this->_autoloader = Engine_Loader::getInstance();
    }

    return $this->_autoloader;
  }

  /**
   * Get the primary bootstrap
   * 
   * @return Engine_Application_Boostrap_Abstract
   * @throws Engine_Application_Exception If the bootstrap has not been configured
   */
  public function getBootstrap()
  {
    if( null === $this->_bootstrap )
    {
      throw new Engine_Application_Exception('No bootstrap registered');
    }

    return $this->_bootstrap;
  }



  // Options
  
  public function getOptions()
  {
    return $this->_options;
  }

  public function setOption($key, $value)
  {
    $this->_options[$key] = $value;
    return $this;
  }

  public function getOption($key, $default = null)
  {
    if( !isset($this->_options[$key]) )
    {
      return $default;
    }

    return $this->_options[$key];
  }

  /**
   * Add php include paths
   * 
   * @param array $paths
   * @return Engine_Application
   */
  public function setIncludePaths(array $paths)
  {
    $path = implode(PATH_SEPARATOR, $paths);
    set_include_path($path . PATH_SEPARATOR . get_include_path());
    return $this;
  }

  /**
   * Set php settings
   * 
   * @param array $settings An array of setting to value
   * @param string $prefix (OPTIONAL) Prefix to use with setting name
   * @return Engine_Application
   */
  public function setPhpSettings(array $settings, $prefix = '')
  {
    $settings = (array) $settings;
    foreach( $settings as $key => $value )
    {
      $key = empty($prefix) ? $key : $prefix . $key;
      if( is_scalar() )
      {
        ini_set($key, $value);
      }
      else if( is_array($value) )
      {
        $this->setPhpSettings($settings, $key . '.');
      }
    }
    return $this;
  }

  /**
   * Sets loader prefixes in the autoloader
   * 
   * @param array $namespaces
   * @return Engine_Application
   */
  public function setAutoloaderNamespaces(array $namespaces)
  {
    foreach( $namespaces as $prefix => $path )
    {
      if( is_numeric($prefix) )
      {
        $prefix = $path;
        $path = null;
      }
      $this->getAutoloader()->register($prefix, $path);
    }
    return $this;
  }

  /**
   * Set bootstrap options
   * 
   * @param array $options
   * @return Engine_Application
   */
  public function setBootstrap(array $options)
  {
    $class = @$options['class'];
    $path = @$options['path'];

    if( !file_exists($path) )
    {
      throw new Engine_Application_Exception('Bootstrap not found');
    }

    require_once $path;

    if( !class_exists($class, false) )
    {
      throw new Engine_Application_Exception('Bootstrap not found');
    }

    $this->_bootstrap = new $class($this);

    return $this;
  }

  /**
   * Strip all input globals of slashes, if magic quotes gps is on
   * 
   * @staticvar boolean $stripped Whether or not we've run yet
   */
  protected static function _stripGlobals()
  {
    static $stripped;
    if( !$stripped && get_magic_quotes_gpc() )
    {
      $_GET     = self::_stripSlashes($_GET);
      $_POST    = self::_stripSlashes($_POST);
      $_COOKIE  = self::_stripSlashes($_COOKIE);
      $_REQUEST = self::_stripSlashes($_REQUEST);
      $stripped = true;
    }
  }

  /**
   * Deep slasher
   * 
   * @param mixed $value
   * @return mixed
   */
  protected static function _stripSlashes($value)
  {
    return ( is_array ( $value ) ? array_map ( array(__CLASS__, '_stripSlashes'), $value ) : stripslashes ( $value ) ) ;
  }
}