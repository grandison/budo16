<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Tasks.php 8432 2011-02-10 00:27:00Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Tasks extends Engine_Db_Table
{
  // Properties
  
  protected $_mode;
  
  protected $_pid;

  protected $_config;

  protected $_external;

  protected $_log;

  protected $_tasks;

  protected $_isExecuting = false;

  protected $_isShutdownRegistered = false;

  protected $_executingTask;

  protected $_runCount = 0;



  // Methods

  // General
  
  public function init()
  {
    // Get configuration
    $this->_config = (array) Engine_Api::_()->getApi('settings', 'core')->getSetting('core.tasks');
    $this->_config = array_merge(array(
      'count'     => 1,       // Max number of tasks run per request
      'countidle' => false,   // Count idle tasks towards the tasks per request
      'interval'  => 15,      // Minimum interval between triggers
      'jobs'      => 3,       // Max number of jobs run per request
      'key'       => '',      // Access key
      'last'      => '',      // Last time trigger was run (or execute in cron mode)
      'mode'      => 'curl',  // Method to trigger execution
      'pid'       => '',      // Random id for trigger and execution mutex
      'processes' => 2,       // Number of allowed concurrent processes
      // @todo add usec support?
      'sleeppre'  => 0,       // (For debug) Seconds to sleep between acquiring task lock and executing task
      'sleepint'  => 0,       // (For debug) Seconds to sleep inside tasks between sub-tasks
      'sleeppost' => 0,       // (For debug) Seconds to sleep after executing task and clearing lock
      'time'      => 120,     // Max time allowed per request (auto adjusts if ini_get('max_execution_time') is available)
      'timeout'   => 900,     // Time before a process is considered rogue
    ), array_filter($this->_config));

    // Make pid
    $this->_pid = $this->_generatePid();
    
    // Get mode
    $this->_mode = ( !empty($this->_config['mode']) ? $this->_config['mode'] : 'curl' );

    // Generate key if missing
    if( empty($this->_config['key']) ) {
      $this->_config['key'] = $this->_generatePid(true);
      Engine_Api::_()->getApi('settings', 'core')->setSetting('core.tasks.key', $this->_config['key']);
    }

    // Adjust time limit if possible
    if( empty($this->_config['time']) || $this->_config['time'] <= 0 ) {
      $this->_config['time'] = 120;
    }
    if( function_exists('ini_get') &&
        ($max_execution_time = ini_get('max_execution_time')) &&
        'cli' !== PHP_SAPI ) {
      if( -1 == $max_execution_time ) {
        // What should we do here
        //$this->_config['time'] = 600;
      } else if( $max_execution_time < $this->_config['time'] ) {
        $this->_config['time'] = floor(0.8 * $max_execution_time);
      }
    }

    // Get default external
    $this->_external = array(
      'key' => null,    // The external access key passed to this request
      'pid' => null,    // The external process id passed to this request
    );
  }

  

  // Config
  
  public function getParam($key, $default = null)
  {
    if( isset($this->_config[$key]) ) {
      return $this->_config[$key];
    } else {
      return $default;
    }
  }
  
  public function getTriggerType()
  {
    switch( $this->_mode ) {
      case 'none':
      case 'cron':
        return false;
        break;
      case 'fork':
      case 'asset':
        return 'pre';
        break;
      case 'curl':
      case 'socket':
      case 'exec':
      default:
        return 'post';
        break;
    }
  }


  
  // Log
  
  /**
   * Get our logger
   * 
   * @return Zend_Log
   */
  public function getLog()
  {
    if( null === $this->_log ) {
      $logAdapter = Engine_Api::_()->getDbtable('settings', 'core')
        ->getSetting('core.log.adapter', 'file');
      $log = new Zend_Log();
      $log->setEventItem('domain', 'tasks');
      try {
        switch( $logAdapter ) {
          case 'file': default:
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/tasks.log'));
            break;
          case 'database':
            $log->addWriter(new Zend_Log_Writer_Db($this->getAdapter(), 'engine4_core_log'));
            break;
          case 'none':
            $log->addWriter(new Zend_Log_Writer_Null());
            break;
        }
      } catch( Exception $e ) {
        $log->addWriter(new Zend_Log_Writer_Null());
      }
      $this->_log = $log;
    }
    return $this->_log;
  }

  public function setLog(Zend_Log $log)
  {
    $this->_log = $log;
    return $this;
  }



  // Tasks

  public function getTasks()
  {
    if( null === $this->_tasks ) {
      $select = $this->select()
        ->where('module IN(?)', (array) Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames())
        ;

      // Order by last?
      
      if( in_array('priority', $this->info('cols')) ) {
        $select->order('priority DESC');
      }

      $this->_tasks = $this->fetchAll($select);
    }
    return $this->_tasks;
  }
  
  public function getTaskPlugin($task)
  {
    // Must be a row of tasks or jobs
    if( !is_object($task) ) {
      throw new Core_Model_Exception(sprintf('Must be given a row of ' .
        'Core_Model_DbTable_Tasks, given ' .
        '%s', gettype($task)));
    } else if( !($task->getTable() instanceof Core_Model_DbTable_Tasks) ) {
      throw new Core_Model_Exception(sprintf('Must be given a row of ' .
          'Core_Model_DbTable_Tasks, given ' .
          '%s', get_class($task)));
    }
    
    // Get plugin class
    $class = $task->plugin;
    
    // Load class
    Engine_Loader::loadClass($class);

    // Make sure is a subclass of Core_Plugin_Task_Abstract
    if( !is_subclass_of($class, 'Core_Plugin_Task_Abstract') ) {
      throw new Core_Model_Exception(sprintf('Task plugin %1$s should extend Core_Plugin_Task_Abstract', $class));
    }

    // Check for execute method?
    if( !method_exists($class, 'execute') ) {
      throw new Core_Model_Exception(sprintf('Task plugin %1$s does not have an execute method', $class));
    }

    // Get plugin object
    $plugin = new $class($task);

    // Set the log
    $plugin->setLog($this->getLog());

    return $plugin;
  }


  
  // Triggering

  public function trigger()
  {
    // Benchmark trigger
    if( 'development' == APPLICATION_ENV ) {
      $start = microtime(true);
    }

    // Do not trigger in cron mode
    if( 'cron' === $this->_mode ) {
      return $this;
    }

    // Check if we should trigger
    if( !$this->_triggerCheck() ) {
      return $this;
    }

    // Get method
    $method = '_triggerMethod' . ucfirst($this->_mode);

    // Unknown mode
    if( !method_exists($this, $method) ) {
      throw new Core_Model_Exception('Unsupported mode: ' . $this->_mode);
    }

    // Set ignore user abort
    $prev = ignore_user_abort(true);

    // Trigger
    $this->$method();

    // Reset ignore user abort
    ignore_user_abort($prev);

    // Benchmark trigger
    if( 'development' == APPLICATION_ENV ) {
      $end = microtime(true);
      $delta = $end - $start;
      $this->getLog()->log(sprintf('Trigger Benchmark [%d] : %f seconds', $this->_pid, $delta), Zend_Log::DEBUG);
    }

    return $this;
  }

  protected function _triggerCheck()
  {
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Trigger Check [%d] ', $this->_pid), Zend_Log::DEBUG);
    }
    
    // Get settings table
    $table = Engine_Api::_()->getDbtable('settings', 'core');

    // Get last
    $last = $table->fetchRow(array('name = ?' => 'core.tasks.last'));
    if( null === $last ) {
      try {
        $table->insert(array(
          'name' => 'core.tasks.last',
          'value' => '',
        ));
      } catch( Exception $e ) {}
    } else {
      $last = $last->value;
    }

    // Get pid
    $pid = $table->fetchRow(array('name = ?' => 'core.tasks.pid'));
    if( null === $pid ) {
      try {
        $table->insert(array(
          'name' => 'core.tasks.pid',
          'value' => '',
        ));
      } catch( Exception $e ) {}
    } else {
      $pid = $pid->value;
    }
    
    // If we are still triggering, make sure delta is larger than the ther interval or timeout
    if( $pid && time() < $last + max($this->_config['interval'], $this->_config['timeout']) ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Trigger Check Failed - Still Triggering + Timeout [%d] - %d < %d',
            $this->_pid, time(), $last + max($this->_config['interval'], $this->_config['timeout'])), Zend_Log::DEBUG);
      }
      return false;
    }
    // Otherwise, if empty, make sure delta is larger than the min of interval and timeout
    else if( !$pid && time() < $last + min($this->_config['interval'], $this->_config['timeout']) ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Trigger Check Failed - Timeout [%d] - %d < %d',
            $this->_pid, time(), $last + min($this->_config['interval'], $this->_config['timeout'])), Zend_Log::DEBUG);
      }
      return false;
    }

    // Acquire lock (pid)
    $affected = $table->update(array(
      'value' => $this->_pid,
    ), array(
      'name = ?' => 'core.tasks.pid',
      'value = ?' => (string) $pid,
    ));

    if( 1 !== $affected ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Trigger Lock Failed [%d] ', $this->_pid), Zend_Log::DEBUG);
      }
      return false;
    }

    // Update last
    $affected = $table->update(array(
      'value' => time(),
    ), array(
      'name = ?' => 'core.tasks.last',
    ));

    // Clear pid lock
    $affected = $table->update(array(
      'value' => '',
    ), array(
      'name = ?' => 'core.tasks.pid',
      'value = ?' => (string) $this->_pid,
    ));

    if( 1 !== $affected ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Trigger Lock Clear Failed [%d] ', $this->_pid), Zend_Log::DEBUG);
      }
    }

    // Check the processes to see if we are above the limit, and whether or not
    // they have timed out
    $processes = $this->getAdapter()->select()
      ->from('engine4_core_processes')
      ->query()
      ->fetchAll()
      ;

    $activeProcesess = 0;
    foreach( $processes as $process ) {
      $started = ( !empty($process['started']) ? $process['started'] : 0 );
      $timeout = ( !empty($process['timeout']) ? $process['timeout'] : $this->_config['timeout'] );
      
      // It's timed out
      if( time() > $started + $timeout ) {
        // Delete
        $this->getAdapter()->delete('engine4_core_processes', array(
          'pid' => $process['pid'],
        ));
        // Log
        if( APPLICATION_ENV == 'development' ) {
          $this->getLog()->log(sprintf('Process Timeout [%d] : %d ', $this->_pid, $process['pid']), Zend_Log::DEBUG);
        }
        continue;
      }

      $activeProcesess++;
    }

    // Process limit reached
    if( $activeProcesess >= $this->_config['processes'] ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Process Limit Reached [%d] : %d ', $this->_pid, $activeProcesess), Zend_Log::DEBUG);
      }
      return false;
    }
    
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Trigger Pass [%d] ', $this->_pid), Zend_Log::DEBUG);
    }
    
    // Okay, let's go!
    return true;
  }

  protected function _triggerMethodCurl()
  {
    global $generalConfig;
    $code = null;
    if( !empty($generalConfig['maintenance']['code']) ) {
      $code = $generalConfig['maintenance']['code'];
    }

    // Setup
    $scheme = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' );
    $host = $_SERVER['HTTP_HOST'];
    if( false !== stripos($_SERVER['SERVER_SOFTWARE'], 'IIS') ) {
      if( !empty($_SERVER['LOCAL_ADDR']) ) {
        $addr = $_SERVER['LOCAL_ADDR'];
      } else if( !empty($_SERVER['SERVER_ADDR']) ) {
        $addr = $_SERVER['SERVER_ADDR'];
      } else if( !empty($_SERVER['HTTP_HOST']) ) {
        $addr = $_SERVER['HTTP_HOST'];
      } else {
        $addr = '127.0.0.1';
      }
    } else {
      $addr = '127.0.0.1';
    }
    $port = ( !empty($_SERVER['SERVER_PORT']) ? (integer) $_SERVER['SERVER_PORT'] : 80 );
    $path = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('controller' => 'utility', 'action' => 'tasks'), 'default', true)
      . '?notrigger=1'
      . '&key=' . $this->_config['key']
      . '&pid=' . $this->_pid
      ;
    // If SSL is enabled, let's turn it off and hope it works
    if( $scheme == 'https://' && $port == 443 ) {
      $scheme = 'http://';
      $port = 80;
    }

    $url = $scheme . $host . $path;
    
    // Set options
    $multi_handle = curl_multi_init();
    $curl_handle = curl_init();

    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_PORT, $port);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Host: ' . $_SERVER['HTTP_HOST']));

    // Try to handle basic htauth
    if( !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) ) {
      curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl_handle, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
    }

    // Try to handle maintenance mode
    if( $code ) {
      curl_setopt($curl_handle, CURLOPT_COOKIE, 'en4_maint_code=' . $code);
    }
    
    curl_multi_add_handle($multi_handle, $curl_handle);
    
    $active = null;
    //execute the handles
    do {
        $mrc = curl_multi_exec($multi_handle, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    
    /*
    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($multi_handle) != -1) {
            do {
                $mrc = curl_multi_exec($multi_handle, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
     * 
     */
  }

  protected function _triggerMethodSocket()
  {
    global $generalConfig;
    $code = null;
    if( !empty($generalConfig['maintenance']['code']) ) {
      $code = $generalConfig['maintenance']['code'];
    }

    // Setup
    $scheme = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' );
    $host = $_SERVER['HTTP_HOST'];
    if( false !== stripos($_SERVER['SERVER_SOFTWARE'], 'IIS') ) {
      if( !empty($_SERVER['LOCAL_ADDR']) ) {
        $addr = $_SERVER['LOCAL_ADDR'];
      } else if( !empty($_SERVER['SERVER_ADDR']) ) {
        $addr = $_SERVER['SERVER_ADDR'];
      } else if( !empty($_SERVER['HTTP_HOST']) ) {
        $addr = $_SERVER['HTTP_HOST'];
      } else {
        $addr = '127.0.0.1';
      }
    } else {
      $addr = '127.0.0.1';
    }
    $port = ( !empty($_SERVER['SERVER_PORT']) ? (integer) $_SERVER['SERVER_PORT'] : 80 );
    $path = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('controller' => 'utility', 'action' => 'tasks'), 'default', true)
      . '?notrigger=1'
      . '&key=' . $this->_config['key']
      . '&pid=' . $this->_pid
      ;
    // If SSL is enabled, let's turn it off and hope it works
    if( $scheme == 'https://' && $port == 443 ) {
      $scheme = 'http://';
      $port = 80;
    }

    $url = $scheme . $host . $path;

    // Connect
    $handle = fsockopen($addr, $port, $errno, $errstr, 0.5);
    stream_set_blocking($handle, 1);
    if( !$handle ) {
      //echo "$errstr ($errno)<br />\n";
      return;
    } else {
      $out = "GET {$path} HTTP/1.1\r\n";
      $out .= "Host: {$host}\r\n";
      if( !empty($code) ) {
        $out .= "Cookie: en4_maint_code={$code}\r\n";
      }
      $out .= "Connection: Close\r\n\r\n";

      fwrite($handle, $out);

      // Can't close or the remote connection will cancel
      //fclose($handle);
    }
  }

  protected function _triggerMethodCron()
  {
    return false;
  }

  protected function _triggerMethodFork()
  {
    if( !function_exists('pcntl_fork') ) {
      $this->getLog()->log('Fork not available', Zend_Log::ERR);
      return;
    }

    // Fork
    $pid = pcntl_fork();
    if( $pid == -1 ) {
      $this->getLog()->log('Could not fork', Zend_Log::ERR);
      //die('could not fork');
    } else if( $pid ) {
      // we are the parent
      //pcntl_wait($status); //Protect against Zombie children
    } else {
      // we are the child
      $this->execute();
      exit();
    }
  }

  protected function _triggerMethodAsset()
  {
    // Get url
    $path = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('controller' => 'utility', 'action' => 'tasks'), 'default', true)
      . '?notrigger=1'
      //. '&key=' . $this->_config['key']
      . '&pid=' . $this->_pid
      . '&mode=' . 'js'
      ;
    $url = $path;

    // Add to headScript
    $headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile($url);
  }

  protected function _triggerMethodExec()
  {
    // Check if exec exists
    if( !function_exists('exec') ) {
      $this->getLog()->log('Could not trigger using CLI, exec is disabled.', Zend_Log::ERR);
      return false;
    }
    
    // @todo should we execute which?
    
    // Run command
    $command = 'php -f' . ' '
      . escapeshellarg(APPLICATION_PATH . '/application/cli.php')
      . ' '
      . escapeshellarg('controller=utility,action=tasks,pid=' . $this->_pid . ',key=' . $this->_config['key'])
      . ' >> ' . escapeshellarg(APPLICATION_PATH . '/temporary/log/tasks.log') . ' 2>&1 &'
      ;

    $this->getLog()->log($command, Zend_Log::ERR);
    
    exec($command);
  }



  // Execution
  
  public function execute($params = null)
  {
    // Benchmark execute
    if( 'development' == APPLICATION_ENV ) {
      $start = microtime(true);
    }

    // Set time limit and ignore_user_abort
    set_time_limit(0);
    $prev = ignore_user_abort(true);
    
    // Register fatal error handler
    if( !$this->_isShutdownRegistered ) {
      register_shutdown_function(array($this, 'handleShutdown'));
      $this->_isShutdownRegistered = true;
    }

    // Signal execution start
    $this->_isExecuting = true;

    // Process params
    $this->_external = array_merge(
      $this->_external,
      array_intersect_key(array_merge($_GET, $_POST, (array) $params), $this->_external)
    );

    // Update last in cron mode for consistency
    if( 'cron' === $this->_mode ) {
      Engine_Api::_()->getDbtable('settings', 'core')->update(array(
        'value' => time(),
      ), array(
        'name = ?' => 'core.tasks.last',
      ));
    }

    // Execute
    if( $this->_executeCheck() ) {

      // Inject current process identifier
      $this->getAdapter()->insert('engine4_core_processes', array(
        'pid' => $this->_pid,
        'parent_pid' => (int) $this->_external['pid'],
        'system_pid' => (int) ( function_exists('posix_getpid') ? posix_getpid() : 0 ),
        'started' => time(),
        'timeout' => 0, // @todo
        'name' => '',
      ));
      
      // Run tasks
      foreach( $this->getTasks() as $task ) {
        // Check if they were run in the background while other tasks were executing
        $task->refresh();
        if( $this->_executeTaskCheck($task) ) {
          $this->_executeTask($task);
        }
      }
      
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Execution Complete [%d] [%d]', $this->_pid, $this->_external['pid']), Zend_Log::DEBUG);
      }
    }

    // Signal execution end
    $this->_isExecuting = false;

    // Restore abort
    ignore_user_abort($prev);

    // Benchmark trigger
    if( 'development' == APPLICATION_ENV ) {
      $end = microtime(true);
      $delta = $end - $start;
      if( !empty($this->slept) ) {
        $deltaUnslept = $delta - $this->slept;
        $this->getLog()->log(sprintf('Execution Benchmark [%d] [%d] : %f seconds (including sleep: %f)', $this->_pid, $this->_external['pid'], $deltaUnslept, $delta), Zend_Log::DEBUG);
      } else {
        $this->getLog()->log(sprintf('Execution Benchmark [%d] [%d] : %f seconds', $this->_pid, $this->_external['pid'], $delta), Zend_Log::DEBUG);
      }
    }

    return $this;
  }

  public function executeTask(Engine_Db_Table_Row $task)
  {
    $data = array();

    if( time() < $task->started_last + $task->timeout ) {
      $data['started_last'] = time() - $task->timeout;
    }

    if( time() < $task->completed_last + $task->timeout ) {
      $data['completed_last'] = time() - $task->timeout;
    }

    if( !empty($data) ) {
      $this->update($data, array(
        'task_id = ?' => $task->task_id,
      ));
    }
    
    return $this;
  }

  protected function _executeCheck()
  {
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Execution Check [%d] [%d] ', $this->_pid, $this->_external['pid']), Zend_Log::DEBUG);
    }

    // Check passkey
    if( 'asset' !== $this->_mode && $this->_external['key'] != $this->_config['key'] ) {
      return false;
    }
    
    // Check processes?
    $activeProcesess = $this->getAdapter()->select()
      ->from('engine4_core_processes', new Zend_Db_Expr('COUNT(pid)'))
      ->query()
      ->fetchColumn()
      ;

    // Process limit reached
    if( $activeProcesess >= $this->getParam('processes', 2) ) {
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Process Limit Reached [%d] : %d ', $this->_pid, $activeProcesess), Zend_Log::DEBUG);
      }
      return false;
    }
    
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Execution Pass [%d] [%d] ', $this->_pid, $this->_external['pid']), Zend_Log::DEBUG);
    }

    return true;
  }
  
  protected function _executeTask(Engine_Db_Table_Row $task)
  {
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Task Execution Check [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
    }

    // Task execution semaphore
    $affected = $this->update(array(
      'started_last' => time(),
      'started_count' => new Zend_Db_Expr('started_count + 1'),
      'semaphore' => new Zend_Db_Expr('semaphore + 1'),
    ), array(
      'task_id = ?' => $task->task_id,
      'semaphore < ?' => new Zend_Db_Expr('processes'),
    ));

    if( 1 !== $affected ) {
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Task Execution Failed Semaphore [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
      }
      return false;
    }

    // Update process identifier
    $affected = $this->getAdapter()->update('engine4_core_processes', array(
      'name' => $task->plugin,
    ), array(
      'pid = ?' => $this->_pid,
    ));

    if( 1 !== $affected ) {
      // Wth?
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Execution Failed Process Update [%d] : %s', $this->_pid, $task->plugin), Zend_Log::DEBUG);
      }
    }

    // Debug: sleeppre
    $slept = 0;
    if( $this->getParam('sleeppre', 0) > 0 ) {
      $slept += $this->getParam('sleeppre');
      sleep($this->getParam('sleeppre'));
    }
    
    // Refresh
    $task->refresh();
    
    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log(sprintf('Task Execution Pass [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
    }



    // ----- MAIN -----
    
    // Set executing task
    $this->_executingTask = $task;

    // Invoke plugin
    $status = false;
    $isComplete = true;
    $wasIdle = false;
    
    try {
      // Get plugin object
      $plugin = $this->getTaskPlugin($task);

      // Execute
      $plugin->execute();

      // Check was idle
      $wasIdle = $plugin->wasIdle();

      // Ok
      $status = true;

    } catch( Exception $e ) {
      // Log exception
      $this->getLog()->log($e->__toString(), Zend_Log::ERR);
      $status = false;
    }

    // ----- MAIN -----



    // Debug: sleeppost
    if( $this->getParam('sleeppost', 0) > 0 ) {
      $slept += $this->getParam('sleeppost');
      sleep($this->getParam('sleeppost'));
    }
    if( !isset($this->slept) ) {
      $this->slept = 0;
    }
    $this->slept += $slept;
    
    // Update process identifier
    $affected = $this->getAdapter()->update('engine4_core_processes', array(
      'name' => '',
    ), array(
      'pid = ?' => $this->_pid,
    ));

    if( 1 !== $affected ) {
      // Wth?
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Execution Failed Process Update (post) [%d] : %s', $this->_pid, $task->plugin), Zend_Log::DEBUG);
      }
    }

    // Update task and release semaphore
    $statusKey = ($status ? 'success' : 'failure');
    $affected = $this->update(array(
      'semaphore' => new Zend_Db_Expr('semaphore - 1'),
      'completed_last' => time(),
      'completed_count' => new Zend_Db_Expr('completed_count + 1'),
      $statusKey . '_last' => time(),
      $statusKey . '_count' => new Zend_Db_Expr($statusKey . '_count + 1'),
    ), array(
      'task_id = ?' => $task->task_id,
      'semaphore > ?' => 0,
    ));

    if( 1 !== $affected ) {
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Task Execution Failed Semaphore Release [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
      }
      return false;
    }
    
    // Update count
    if( !$wasIdle ) {
      $this->_runCount++;
    }
    
    // Remove executing task
    $this->_executingTask = null;

    // Log
    if( APPLICATION_ENV == 'development' ) {
      if( $status ) {
        $this->getLog()->log(sprintf('Task Execution Complete [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
      } else {
        $this->getLog()->log(sprintf('Task Execution Complete with errors [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
      }
    }

    return $this;
  }



  // Utility
  
  public function handleShutdown()
  {
    // Clear process identifier
    try {
      $this->getAdapter()->delete('engine4_core_processes', array(
        'pid = ?' => $this->_pid,
      ));
    } catch( Exception $e ) {
      $this->getLog()->log('Error clearing pid: ' . $e->__toString(), Zend_Log::ERR);
    }

    // There was no error during execution
    if( !$this->_isExecuting ) {
      return;
    }
    $this->_isExecuting = false;

    // This means there was a fatal error during execution
    $db = $this->getAdapter();

    // Log
    //if( APPLICATION_ENV == 'development' ) {
      $message = '';
      if( function_exists('error_get_last') ) {
        $message = error_get_last();
        $message = $message['type'] . ' ' . $message['message'] . ' ' . $message['file'] . ' ' . $message['line'];
      }
      $this->getLog()->log('Execution Error: ' . $this->_pid . ' - ' . $message, Zend_Log::ERR);
    //}

    // Let's call rollback just in case the fatal error happened inside a transaction
    // This will restore autocommit
    try {
      $db->rollBack();
    } catch( Exception $e ) {
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Shutdown failed rollback [%d]', $this->_pid), Zend_Log::DEBUG);
      }
    }

    // There was no task executing during error
    if( !($this->_executingTask instanceof Zend_Db_Table_Row_Abstract) ) {
      return;
    }

    // Cleanup executing task
    $task = $this->_executingTask;

    // Update task and release semaphore
    $statusKey = (false ? 'success' : 'failure');
    $affected = $this->update(array(
      'semaphore' => new Zend_Db_Expr('semaphore - 1'),
      'completed_last' => time(),
      'completed_count' => new Zend_Db_Expr('completed_count + 1'),
      $statusKey . '_last' => time(),
      $statusKey . '_count' => new Zend_Db_Expr($statusKey . '_count + 1'),
    ), array(
      'task_id = ?' => $task->task_id,
      'semaphore > ?' => 0,
    ));

    if( 1 !== $affected ) {
      if( APPLICATION_ENV == 'development' ) {
        $this->getLog()->log(sprintf('Task Execution Failed Semaphore Release [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
      }
      return false;
    }
  }
  
  protected function _executeTaskCheck(Engine_Db_Table_Row $task)
  {
    // We've executed at least as many tasks as count
    if( $this->_runCount >= $this->_config['count'] ) {
      return false;
    }

    // We've reached the time limit for this request
    if( microtime(true) >= _ENGINE_REQUEST_START + $this->_config['time'] ) {
      return false;
    }
    
    // Task is not ready to be executed again yet
    if( $task->timeout > 0 ) {
      if( time() < $task->started_last + $task->timeout ) {
        return false;
      }
      if( time() < $task->completed_last + $task->timeout ) {
        return false;
      }
    }

    // If semaphore limit is reached, and the timeout
    // has been reached, check if lock needs to be cleared
    if( $task->semaphore >= $task->processes ) {
      // Sanity - wth is this?
      if( $task->processes < 1 ) {
        $task->processes = 1;
        $task->save();
        return false;
      }

      // Get all processes matching task plugin
      $taskProcesses = $this->getAdapter()->select()
        ->from('engine4_core_processes')
        ->where('name = ?', $task->plugin)
        ->query()
        ->fetchAll();

      // There was nothing, flush mutexes
      if( empty($taskProcesses) ) {
        $affected = $this->update(array(
          'semaphore' => new Zend_Db_Expr(sprintf('semaphore - %d', $task->semaphore)),
        ), array(
          'task_id = ?' => $task->task_id,
        ));
        
        if( 1 !== $affected ) {
          // Log
          if( APPLICATION_ENV == 'development' ) {
            $this->getLog()->log(sprintf('Execution Mutex Flush Failed [%d] : %s', $this->_pid, $task->title), Zend_Log::DEBUG);
          }
          return false;
        }
      }

      // Check each process
      else {
        $activeProcesses = 0;
        foreach( $taskProcesses as $process ) {
          $started = ( !empty($process['started']) ? $process['started'] : 0 );
          $timeout = ( !empty($process['timeout']) ? $process['timeout'] : $this->_config['timeout'] );

          // It's timed out
          if( time() > $started + $timeout ) {
            // Delete
            $this->getAdapter()->delete('engine4_core_processes', array(
              'pid' => $process['pid'],
            ));
            // Log
            if( APPLICATION_ENV == 'development' ) {
              $this->getLog()->log(sprintf('Process Timeout [%d] : %d ', $this->_pid, $process['pid']), Zend_Log::DEBUG);
            }
            continue;
          }
          
          $activeProcesses++;
        }
        if( $activeProcesses >= $task->processes ) {
          // Log
          if( APPLICATION_ENV == 'development' ) {
            $this->getLog()->log(sprintf('Execution Process Flush Failed [%d] : %d ', $this->_pid, $activeProcesses), Zend_Log::DEBUG);
          }
          return false;
        }
      }
    }
    
    // Task is ready
    return true;
  }

  public function reset($tasks = null, $includeStats = false)
  {
    // Update global locks
    Engine_Api::_()->getDbtable('settings', 'core')->update(array(
      'value' => '',
    ), array(
      'name IN(?)' => array('core.tasks.pid', 'core.tasks.last'),
    ));
    
    if( null !== $tasks ) {

      if( !is_array($tasks) && !($tasks instanceof Zend_Db_Table_Rowset_Abstract) ) {
        $tasks = array($tasks);
      }

      $ids = array();
      foreach( $tasks as $task ) {
        if( is_numeric($task) ) {
          $ids[] = $task;
        } else if( $task instanceof Zend_Db_Table_Row_Abstract &&
            $task->getTable() instanceof Core_Model_DbTable_Tasks ) {
          $ids[] = $task->task_id;
        }
      }
      $tasks = $ids;
    }

    if( null === $tasks || !empty($tasks) ) {
      $where = array();
      if( null === $tasks ) {
        $where = null;
      } else if( empty($tasks) ) {
        return;
      } else if( is_numeric($tasks) ) {
        $where['task_id = ?'] = $tasks;
      } else if( is_array($tasks) ) {
        $where['task_id IN(?)'] = $tasks;
      } else {
        return;
      }

      $data = array(
        'semaphore' => 0,
        'started_last' => 0,
        'completed_last' => 0,
      );

      if( $includeStats ) {
        $data = array_merge($data, array(
          'started_count' => 0,
          'completed_count' => 0,
          'failure_last' => 0,
          'failure_count' => 0,
          'success_last' => 0,
          'success_count' => 0,
        ));
      }
      
      $this->update($data, $where);
    }
    
    return $this;
  }



  // Utility

  protected function _generatePid($asHex = false)
  {
    $max = min(mt_getrandmax(), 2147483647); // Do not allow more than 32bit unsigned
    $val = mt_rand(0, $max);
    if( $asHex ) {
      return str_pad(base_convert($val, 10, 16), 8, '0', STR_PAD_LEFT);
    } else {
      return str_pad(sprintf('%u', $val), 10, '0', STR_PAD_LEFT);
    }
  }
}