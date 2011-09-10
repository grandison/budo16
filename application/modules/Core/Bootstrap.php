<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Bootstrap.php 8791 2011-04-05 22:56:38Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application)
  {
    parent::__construct($application);
    ///$this->initPluginResourcePath();
    //date_default_timezone_set('America/Los_Angeles');
    date_default_timezone_set('UTC');
    if( function_exists('mb_internal_encoding') ) {
      mb_internal_encoding("UTF-8");
    }
    if( function_exists('iconv_set_encoding') ) {
      // Not sure if we want to do all of these
      iconv_set_encoding("input_encoding", "UTF-8");
      iconv_set_encoding("output_encoding", "UTF-8");
      iconv_set_encoding("internal_encoding", "UTF-8");
    }
    
    /**
     * No idea where this should go right now. Must be intialized after module
     * include paths are set up
    // Set up plugin loader cache
    if( APPLICATION_ENV != 'development' )
    {
      $classFileIncCache = APPLICATION_PATH . '/temporary/pluginLoaderCache.php';
      if( file_exists($classFileIncCache) )
      {
        include_once $classFileIncCache;
      }
      Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
    }
     */
  }

  public function run()
  {
    // Ensure frontcontroller and router are bootstrapped
    $this->bootstrap('frontcontroller');
    $this->bootstrap('router');
    $front = $this->getContainer()->frontcontroller;
    
    // Trigger tasks
    if( !defined('ENGINE_TASK_NOTRIGGER') ) {
      // Get the request so we can get the params
      if( null === ($request = $front->getRequest()) ) {
        $request = new Zend_Controller_Request_Http();
        $front->setRequest($request);
      }
      if( $request->getParam('notrigger') ) {
        define('ENGINE_TASK_NOTRIGGER', true);
      }

      // Actually trigger now
      $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');
      if( $tasksTable->getTriggerType() == 'pre' ) {
        Engine_Api::_()->getDbtable('tasks', 'core')->trigger();
      }
    }

    // Start main
    $default = $front->getDefaultModule();
    if (null === $front->getControllerDirectory($default)) {
        throw new Zend_Application_Bootstrap_Exception(
            'No default controller directory registered with front controller'
        );
    }
    // End main

    // Start main 2
    $front->setParam('bootstrap', $this);
    $front->dispatch();
    // End main 2
    
    // Trigger tasks
    if( !defined('ENGINE_TASK_NOTRIGGER') ) {
      if( $tasksTable->getTriggerType() == 'post' ) {
        Engine_Api::_()->getDbtable('tasks', 'core')->trigger();
      }
    }
    
    // Close the session to prevent chicken-egg
    // http://bugs.php.net/bug.php?id=33772
    Zend_Session::writeClose();
  }

  protected function _initDb()
  {
    $file = APPLICATION_PATH . '/application/settings/database.php';
    $options = include $file;

    $db = Zend_Db::factory($options['adapter'], $options['params']);
    Engine_Db_Table::setDefaultAdapter($db);
    Engine_Db_Table::setTablePrefix($options['tablePrefix']);
    
    // Non-production
    if( APPLICATION_ENV !== 'production' ) {
      $db->setProfiler(array(
        'class' => 'Zend_Db_Profiler_Firebug',
        'enabled' => true
      ));
    }

    // set DB to UTC timezone for this session
    switch( $options['adapter'] ) {
      case 'mysqli':
      case 'mysql':
      case 'pdo_mysql': {
          $db->query("SET time_zone = '+0:00'");
          break;
      }

      case 'postgresql': {
          $db->query("SET time_zone = '+0:00'");
          break;
      }

      default: {
        // do nothing
      }
    }

    // attempt to disable strict mode
    try {
      $db->query("SET SQL_MODE = ''");
    } catch (Exception $e) {}

    return $db;
  }

  protected function _initNode()
  {
    // @todo revisit this for cloud hosting
    return;
    try {
      $db = Engine_Db_Table::getDefaultAdapter();
      
      // Check for signature
      $signatureFile = APPLICATION_PATH . '/application/settings/node.php';
      if( file_exists($signatureFile) ) {
        $signature = file_get_contents($signatureFile);
        $writeable = is_writable($signatureFile);
      } else {
        $signature = null;
        $writeable = is_writable(dirname($signatureFile));
      }

      // Verify signature exists
      $node_id = null;
      if( $signature ) {
        $node_id = $db->select()
          ->from('engine4_core_nodes', 'node_id')
          ->where('signature = ?', $signature)
          ->query()
          ->fetchColumn();
        if( !$node_id ) {
          $signature = null;
        }
      }

      // Update signature
      if( $signature ) {
        $db->update('engine4_core_nodes', array(
          'last_seen' => new Zend_Db_Expr('NOW()'),
        ), array(
          'node_id = ?' => $node_id,
        ));
        Zend_Registry::set('Engine_Node', $node_id);
      }

      // Create signature
      else if( $writeable ) {
        $signature = sha1((function_exists('php_uname') ? php_uname() : '')
            . $_SERVER['SERVER_ADDR']
            . time());

        $db->insert('engine4_core_nodes', array(
          'signature' => $signature,
          'host' => $_SERVER['HTTP_HOST'],
          'ip' => ip2long($_SERVER['SERVER_ADDR']),
          'first_seen' => new Zend_Db_Expr('NOW()'),
          'last_seen' => new Zend_Db_Expr('NOW()'),
        ));

        $node_id = $db->lastInsertId();

        file_put_contents($signatureFile, $signature);

        Zend_Registry::set('Engine_Node', $node_id);
      }

      // Failure
      else {
        Zend_Registry::set('Engine_Node', false);
      }
    } catch( Exception $e ) {
      // Silence?
    }
  }

  protected function _initFrontController()
  {
    Zend_Controller_Action_HelperBroker::addPath("Engine/Controller/Action/Helper/", 'Engine_Controller_Action_Helper');

    $frontController = Zend_Controller_Front::getInstance();
    $frontController
      //->addModuleDirectory(APPLICATION_PATH . "/application/modules/")
      ->setDefaultModule('core')
      ->setParam('viewSuffix', 'tpl')
      ->setParam('prefixDefaultModule', 'true');

    // Add our special path for action helpers
    $this->initActionHelperPath();

    // Our virtual index hack confuses the request class, this other hack will
    // make it think it's in the root folder
    $request = new Zend_Controller_Request_Http();
    $script = $_SERVER['SCRIPT_NAME'];
    $_SERVER['SCRIPT_NAME'] = str_replace('/application/', '/', $script);
    $frontController->setBaseUrl($request->getBaseUrl());
    $_SERVER['SCRIPT_NAME'] = $script;
    
    // Save to registy and local container
    Zend_Registry::set('Zend_Controller_Front', $frontController);
    return $frontController;
  }

  protected function _initCache()
  {
    // Get configurations
    $file = APPLICATION_PATH . '/application/settings/cache.php';

    // @todo cache config in database

    if( file_exists($file) ) {
      // Manual config
      $options = include $file;
    } else if( is_writable(APPLICATION_PATH . '/temporary/cache') || (
        !@is_dir(APPLICATION_PATH . '/temporary/cache') &&
        @mkdir(APPLICATION_PATH . '/temporary/cache', 0777, true)
        ) ) {
      // Auto default config
      $options = array(
        'default_backend' => 'File',
        'frontend' => array (
          'core' => array (
            'automatic_serialization' => true,
            'cache_id_prefix' => 'Engine4_',
            'lifetime' => '300',
            'caching' => true,
          ),
        ),
        'backend' => array(
          'File' => array(
            'cache_dir' => APPLICATION_PATH . '/temporary/cache',
          ),
        ),
      );
    } else {
      // Failure
      return null;
    }

    // Create cache
    $frontend = key($options['frontend']);
    $backend = key($options['backend']);
    Engine_Cache::setConfig($options);
    $cache = Engine_Cache::factory($frontend, $backend);

    // Disable caching in development mode
    if( APPLICATION_ENV == 'development' ) {
      $cache->setOption('caching', false);
    }

    // Save in registry
    Zend_Registry::set('Zend_Cache', $cache);

    // Use cache helper?
    Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-1, new Engine_Controller_Action_Helper_Cache());
    
    // Add cache to database
    Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

    // Save in bootstrap
    return $cache;
  }

  protected function _initLog()
  {
    $log = new Zend_Log();
    $log->setEventItem('domain', 'error');

    // Non-production
    if( APPLICATION_ENV !== 'production' ) {
      $log->addWriter(new Zend_Log_Writer_Firebug());
    }

    // Get log config
    $db = Engine_Db_Table::getDefaultAdapter();
    $logAdapter = $db->select()
      ->from('engine4_core_settings', 'value')
      ->where('`name` = ?', 'core.log.adapter')
      ->query()
      ->fetchColumn();
    
    // Set up log
    switch( $logAdapter ) {
      case 'database': {
        try {
          $log->addWriter(new Zend_Log_Writer_Db($db, 'engine4_core_log'));
        } catch( Exception $e ) {
          // Make sure logging doesn't cause exceptions
          $log->addWriter(new Zend_Log_Writer_Null());
        }
        break;
      }
      default:
      case 'file': {
        try {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/main.log'));
        } catch( Exception $e ) {
          // Check directory
          if( !@is_dir(APPLICATION_PATH . '/temporary/log') &&
              @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true) ) {
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/main.log'));
          } else {
            // Silence ...
            if( APPLICATION_ENV !== 'production' ) {
              $log->log($e->__toString(), Zend_Log::CRIT);
            } else {
              // Make sure logging doesn't cause exceptions
              $log->addWriter(new Zend_Log_Writer_Null());
            }
          }
        }
        break;
      }
      case 'none': {
        $log->addWriter(new Zend_Log_Writer_Null());
        break;
      }
    }

    // Save to registry
    Zend_Registry::set('Zend_Log', $log);

    // Register error handlers
    Engine_Api::registerErrorHandlers();

    if( 'production' != APPLICATION_ENV ) {
      Engine_Exception::setLog($log);
    }

    return $log;
  }

  protected function _initFrontControllerModules()
  {
    $frontController = Zend_Controller_Front::getInstance();
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . "application" . DIRECTORY_SEPARATOR ."modules";

    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    foreach( $enabledModuleNames as $module )
    {
      $moduleInflected = Engine_Api::inflect($module);
      $moduleDir = $path . DIRECTORY_SEPARATOR . $moduleInflected; // @todo proper inflection
      if( is_dir($moduleDir) ) {
        $moduleDir .= DIRECTORY_SEPARATOR . $frontController->getModuleControllerDirectoryName();
        $frontController->addControllerDirectory($moduleDir, $module);
      } else {
        // Maybe we should log modules that fail to load?
        if( APPLICATION_ENV == 'development' ) {
          throw new Engine_Exception('failed to load module "' . $module . '"');
        }
      }
    }

    $frontController
      //->addModuleDirectory(APPLICATION_PATH . "/application/modules/")
      ->setDefaultModule('core');
  }

  protected function _initManifest()
  {
    // Load from cache
    $cached = false;
    
    if( isset($this->getContainer()->cache) )
    {
      $data = $this->getContainer()->cache->load('Engine_Manifest');
      if( is_array($data) )
      {
        $manifest = $data;
        //$manifest = new Zend_Config($data);
        $cached = true;
      }
    }

    // Load manually
    if( !$cached )
    {
      $data = array();
      foreach( $this->getContainer()->frontcontroller->getControllerDirectory() as $name => $path )
      {
        $file = dirname($path) . '/settings/manifest.php';
        if( file_exists($file) )
        {
          $data[$name] = include($file);
        }
        else
        {
          $data[$name] = array();
        }
      }
      $manifest = $data;
      //$manifest = new Zend_Config($data);
    }

    Zend_Registry::set('Engine_Manifest', $manifest);

    // Save to cache
    if( !$cached && isset($this->getContainer()->cache) )
    {
      $this->getContainer()->cache->save(serialize($manifest), 'Engine_Manifest');
      //$this->getContainer()->cache->save(serialize($manifest->toArray()), 'Engine_Manifest');
    }

    return $data;
  }

  protected function _initSession()
  {
    // Get session configuration
    $file = APPLICATION_PATH . '/application/settings/session.php';
    $config = array();
    if( file_exists($file) ) {
      $config = include $file;
    }

    // Get default session configuration
    if( empty($config) ) {
      $config = array(
        'options' => array(
          'save_path' => 'session',
          'use_only_cookies' => true,
          'remember_me_seconds' => 864000,
          'gc_maxlifetime' => 86400,
          'cookie_httponly' => false,
        ),
        'saveHandler' => array(
          'class' => 'Core_Model_DbTable_Session',
          'params' => array(
            'lifetime' => 86400,
          ),
        ),
      );
    }
    
    // Remove httponly unless forced in config
    if( !isset($config['options']['cookie_httponly']) ) {
      $config['options']['cookie_httponly'] = false;
    }

    // Set session options
    Zend_Session::setOptions($config['options']);
    
    $saveHandler = $config['saveHandler']['class'];
    Zend_Session::setSaveHandler(new $saveHandler($config['saveHandler']['params']));

    // Session hack for fancy upload
    //if( !isset($_COOKIE[session_name()]) )
    //{
      $sessionName = Zend_Session::getOptions('name');
      if( isset($_POST[$sessionName]) ) {
        Zend_Session::setId($_POST[$sessionName]);
      } else if( isset($_POST['PHPSESSID']) ) {
        Zend_Session::setId($_POST['PHPSESSID']);
      }
    //}

    //Zend_Session::start();
  }

  protected function _initRouter()
  {
    $router = $this->getContainer()->frontcontroller->getRouter();
    
    $defaultAdminRoute = Engine_Controller_Router_Route_ControllerPrefix::getInstance(new Zend_Config(array()));
    $router->addRoute('admin_default', $defaultAdminRoute);

    // Add module-configured routes
    $manifest = Zend_Registry::get('Engine_Manifest');
    foreach( $manifest as $module => $config )
    {
      if( !isset($config['routes']) )
      {
        continue;
      }
      $router->addConfig(new Zend_Config($config['routes']));
      //$router->addConfig($config->routes);
    }

    // Add user-defined routes
    $routesTable = Engine_Api::_()->getDbtable('routes', 'core');
    $userConfig = array();
    foreach( $routesTable->fetchAll($routesTable->select()->order('order')) as $row )
    {
      $data = $row->config;
      $userConfig[$row->name] = $row->config;
    }

    $router->addConfig(new Zend_Config($userConfig));

    // Add default routes
    $router->addDefaultRoutes();
    
    return $router;
  }

  protected function _initView()
  {
    // Create view
    $view = new Zend_View();

    // Set encoding (@todo maybe use configuration?)
    $view->setEncoding('utf-8');

    $view->addScriptPath(APPLICATION_PATH);

    // Setup and register viewRenderer
    // @todo we may not need to override zend's
    $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
    //$viewRenderer = new Engine_Controller_Action_Helper_ViewRenderer($view);
    $viewRenderer->setViewSuffix('tpl');
    Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);

    // Initialize contextSwitch helper
    Zend_Controller_Action_HelperBroker::addHelper(new Core_Controller_Action_Helper_ContextSwitch());

    // Add default helper paths
    $view->addHelperPath('Engine/View/Helper/', 'Engine_View_Helper_');
    $this->initViewHelperPath();

    // Set doctype
    Engine_Loader::loadClass('Zend_View_Helper_Doctype');
    $doctypeHelper = new Zend_View_Helper_Doctype();
    $doctypeHelper->doctype(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.doctype', 'HTML4_LOOSE'));
    
    // Add to local container and registry
    Zend_Registry::set('Zend_View', $view);
    return $view;
  }
  
  protected function _initLayout()
  {
    // Create layout
    $layout = Zend_Layout::startMvc();

    // Set options
    $layout->setViewBasePath(APPLICATION_PATH . "/application/modules/Core/layouts", 'Core_Layout_View')
      ->setViewSuffix('tpl')
      ->setLayout(null);

    // Add themes
    $theme = null;
    $themes = array();
    $themesInfo = array();

    $themeTable = Engine_Api::_()->getDbtable('themes', 'core');
    if( !empty($_COOKIE['theme']) && is_numeric($_COOKIE['theme']) ) {
      $theme = $themeTable->find((int) $_COOKIE['theme'])->current();
    }
    if( !$theme ) {
      $themeSelect = $themeTable->select()
        ->where('active = ?', 1)
        ->limit(1);
      $theme = $themeTable->fetchRow($themeSelect);
    }

    if( $theme ) {
      $themes[] = $theme->name;
      $themesInfo[$theme->name] = include APPLICATION_PATH_COR . DS
          . 'themes' . DS . $theme->name . DS . 'manifest.php';
    }
    
    $layout->themes = $themes;
    $layout->themesInfo = $themesInfo;
    Zend_Registry::set('Themes', $themesInfo);

    // Add global site title etc
    $siteinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site', array());
    $siteinfo = array_filter($siteinfo);
    $siteinfo = array_merge(array(
      'title' => 'Social Network',
      'description' => '',
      'keywords' => '',
    ), $siteinfo);
    $layout->siteinfo = $siteinfo;

    // Get global site revision counter
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $counter = $settings->core_site_counter;
    if( !$counter ) {
      $settings->core_site_counter = $counter = 1;
    }
    $layout->counter = $counter;
    
    return $layout;
  }
  /**
   * Initializes translator
   *
   * @return Zend_Translate_Adapter
   */
  public function _initTranslate()
  {
    // Create a log instance for untranslated messages
    //$writer = new Zend_Log_Writer_Firebug();
    //$log    = new Zend_Log($writer);

    // Create translate
    /*
    $translate = new Engine_Translate(array(
      //'scan' => Zend_Translate::LOCALE_FILENAME,
      'log' => $this->getContainer()->log, //$log,
      'logUntranslated' => ( APPLICATION_ENV === 'development' )
    ));
     */

    if( isset($this->getContainer()->cache) )
    {
      Zend_Translate::setCache($this->getContainer()->cache);
    }

    // If in development, log untranslated messages
    $params = array(
      'scan' => Zend_Translate_Adapter::LOCALE_DIRECTORY,
      'logUntranslated' => true
    );

    $log = new Zend_Log();
    if( APPLICATION_ENV == 'development' ) {
      $log = new Zend_Log();
      $log->addWriter(new Zend_Log_Writer_Firebug());
      $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/translate.log'));
    } else {
      $log->addWriter(new Zend_Log_Writer_Null());
    }
    $params['log'] = $log;

    $translate = new Zend_Translate(
      'Csv',
      APPLICATION_PATH.'/application/languages',
      null,
      $params
    );

    Zend_Registry::set('Zend_Translate', $translate);
    
    Zend_Validate_Abstract::setDefaultTranslator($translate);
    Zend_Form::setDefaultTranslator($translate);
    Zend_Controller_Router_Route::setDefaultTranslator($translate);
    
    return $translate;
  }
  
  protected function _initContent()
  {
    $content = Engine_Content::getInstance();

    // Set storage
    $contentTable = Engine_Api::_()->getDbtable('pages', 'core');
    $content->setStorage($contentTable);

    // Load content helper
    $contentRenderer = new Engine_Content_Controller_Action_Helper_Content();
    $contentRenderer->setContent($content);
    Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-85, $contentRenderer);

    // Set cache object
    if( isset($this->getContainer()->cache) ) {
      $content->setCache($this->getContainer()->cache);
    }

    // Set translator
    if( isset($this->getContainer()->translate) ) {
      $content->setTranslator($this->getContainer()->translate);
    }

    // Save to registry
    Zend_Registry::set('Engine_Content', $content);

    return $content;
  }

  protected function _initPaginator()
  {
    // Set up default paginator options
    Zend_Paginator::setDefaultScrollingStyle('Sliding');
    Zend_View_Helper_PaginationControl::setDefaultViewPartial(array(
      'pagination/search.tpl',
      'core'
    ));
  }

  protected function _initHooks()
  {
    $hooks = Engine_Hooks_Dispatcher::getInstance();
    
    // Add module-configured routes
    $manifest = Zend_Registry::get('Engine_Manifest');
    foreach( $manifest as $module => $config )
    {
      if( !isset($config['hooks']) )
      {
        continue;
      }
      $hooks->addEvents($config['hooks']);
    }
    
    return $hooks;
  }

  protected function _initApi()
  {
    return Engine_Api::_();
  }

  protected function _initModules()
  {
    //$front = null;
    //$default = 'core';
    //if( isset($this->getContainer()->frontcontroller) ) {
      $front = $this->getContainer()->frontcontroller;
      $default = $front->getDefaultModule();
    //}
    $bootstraps = new ArrayObject();

    // Prepare data
    $modules = Engine_Api::_()->getDbtable('modules', 'core')->fetchAll();
    $baseDir = APPLICATION_PATH;
    //$baseUrl = preg_replace('/[\/]*index\.php[\/]*/', '/', $front->getBaseUrl());

    foreach( $modules as $module )
    {
      if( !$module->enabled ) continue;
      $module = $module->name;
      $moduleInflected = Engine_Api::inflect($module);
      $moduleDir = $baseDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleInflected;

      // Default module is already bootstrapped, but bootstrap others
      if( strtolower($module) !== strtolower($default) )
      {
        $bootstrapClass = $moduleInflected . '_Bootstrap';

        if( !class_exists($bootstrapClass, false) )
        {
          $bootstrapPath  = $moduleDir . '/Bootstrap.php';
          if( file_exists($bootstrapPath) )
          {
            include_once $bootstrapPath;
            if( !class_exists($bootstrapClass, false) )
            {
              throw new Zend_Application_Resource_Exception('Bootstrap file found for module "' . $module . '" but bootstrap class "' . $bootstrapClass . '" not found');
            }
          }
          else
          {
            continue;
          }
        }

        $moduleBootstrap = new $bootstrapClass($this);
        $moduleBootstrap->bootstrap();
        $bootstraps[$module] = $moduleBootstrap;
      }
    }

    return $bootstraps;
  }

  protected function _initLocale()
  {
    // Translate needs to be initialized before Modules, so _initTranslate() could
    // not load the "User" couldn't be initialized then.  Thus, we must assign
    // the language over here if it is a user.
    
    // Try to pull from various sources
    $viewer   = Engine_Api::_()->user()->getViewer();
    $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
    if( $viewer->getIdentity() ) {
      $locale = $viewer->locale;
      $language = $viewer->language;
      $timezone = $viewer->timezone;
    } else if( !empty($_COOKIE['en4_language']) && !empty($_COOKIE['en4_locale']) ) {
      $locale = $_COOKIE['en4_locale'];
      $language = $_COOKIE['en4_language'];
    } else {
      $locale = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.default', 'auto');
      $language = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'auto');
     }
    Zend_Registry::set('timezone', $timezone);

    // Make sure it's valid
    try {
      $locale = Zend_Locale::findLocale($locale);
    } catch( Exception $e ) {
      $locale = 'en_US';
    }
    try {
      $language = Zend_Locale::findLocale($language);
    } catch( Exception $e ) {
      $language = 'en_US';
    }

    // Set in locale and language
    $this->getContainer()->translate->setLocale($language);

    $localeObject = new Zend_Locale($locale);
    Zend_Registry::set('Locale', $localeObject);
    
    // Set in locale and language
    $translate = $this->getContainer()->translate;
    $defaultLanguage  = Engine_Api::_()->getApi('settings', 'core')->core_locale_locale;
    $selectedLanguage = $localeObject->getLanguage();
    if ( ($selectedLocale = $localeObject->getRegion()) ) {
      $selectedLanguage .= "_$selectedLocale";
    }
    
    if (!$translate->isAvailable($selectedLanguage)) {
      if( !$translate->isAvailable($defaultLanguage) ){
        $translate->setLocale('en_US');
      } else {
        $translate->setLocale($defaultLanguage);
      }
    } else {
      $translate->setLocale($language);
    }

    // Set cache
    Zend_Locale_Data::setCache($this->getContainer()->cache);

    // Get orientation
    $localeData = Zend_Locale_Data::getList($localeObject->__toString(), 'layout');
    $this->getContainer()->layout->orientation = $localeData['characters'];
    
    return $localeObject;
  }

  protected function _initCensor()
  {
    $bannedWords = null;
    
    // caching
    $cache = $this->getContainer()->cache;
    if( $cache instanceof Zend_Cache_Core &&
        ($data = $cache->load('bannedwords')) &&
        is_string($data) ) {
      $bannedWords = $data;
    } else {
      $bannedWords = Engine_Api::_()->getApi('settings', 'core')->core_spam_censor;
      
      $db = $this->getContainer()->db;
      if( $db instanceof Zend_Db_Adapter_Abstract ) {
        $dbBannedWords = $db->select()
            ->from('engine4_core_bannedwords', 'word')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);
        $bannedWords .= ',' . join(',', $dbBannedWords);
      }
      
      $bannedWords = trim($bannedWords, ' ,');

      // save
      $cache->save($bannedWords, 'bannedwords');
    }
    
    Engine_Filter_Censor::setDefaultForbiddenWords($bannedWords);
  }
  
  protected function _initBannedIps()
  {
    // No CLI
    if( 'cli' === PHP_SAPI ) {
      return;
    }
    
    // check if user is banned by IP
    $visitor_ip = ip2long($_SERVER['REMOTE_ADDR']);
    $list       = explode(',', Engine_Api::_()->getApi('settings', 'core')->core_spam_ipbans);
    if( empty($list) )
      return;

    // innocent until proven guilty
    $banned = false;

    // Sort into banned specific IPs and ranges
    $banned_ip_addr   = array();
    $banned_ip_ranges = array();

    foreach( $list as $banned_ip ) {
      $banned_ip = trim($banned_ip);
      if( strpos($banned_ip, '*') !== false ) {
        // Decode
        $tmp_low  = ip2long(str_replace('*', '0', $banned_ip));
        $tmp_high = ip2long(str_replace('*', '255', $banned_ip)); // this might not work for some bytes

        // If failed to decode, or low is larger than high, skip
        if( !$tmp_low || !$tmp_high || $tmp_low > $tmp_high )
        {
          continue;
        }

        // Add
        $banned_ip_ranges[] = array(
          $tmp_low,
          $tmp_high
        );
      } else if( strpos($banned_ip, '-') !== false ) {
        // Decode
        list($tmp_low, $tmp_high) = explode('-', $banned_ip, 2);
        $tmp_low  = ip2long(trim($tmp_low));
        $tmp_high = ip2long(trim($tmp_high));

        // If failed to decode, or low is larger than high, skip
        if( !$tmp_low || !$tmp_high || $tmp_low > $tmp_high ) {
          continue;
        }

        // Add
        $banned_ip_ranges[] = array(
          $tmp_low,
          $tmp_high
        );
      } else {
        $tmp = ip2long($banned_ip);
        if( $tmp )
        {
          $banned_ip_addr[] = $tmp;
        }
      }
    }

    if( in_array($visitor_ip, $banned_ip_addr) ) {
      $banned = true;
    } else {
      foreach( $banned_ip_ranges as $range ) {
        if( $visitor_ip >= $range[0] && $visitor_ip <= $range[1] ) {
          $banned = true;
        }
      }
    }



    // Check database table
    // @todo should we do any sort of caching on this?
    $db = $this->getContainer()->db;
    if( $db instanceof Zend_Db_Adapter_Abstract ) {
      $bannedip_id = $db->select()
          ->from('engine4_core_bannedips', 'bannedip_id')
          ->where('start <= ?', $visitor_ip)
          ->where('stop >= ?', $visitor_ip)
          ->query()
          ->fetchColumn();

      if( $bannedip_id ) {
        $banned = true;
      }
    }


    // tell them they're banned
    if( $banned ) {
      //@todo give appropriate forbidden page
      if( !headers_sent() ) {
        header('HTTP/1.0 403 Forbidden');
      }
      die('banned');
    }
  }

}