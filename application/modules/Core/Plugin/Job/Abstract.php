<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Plugin_Job_Abstract
{
  /**
   * @var Zend_Db_Table_Row_Abstract
   */
  protected $_job;

  /**
   * @var Zend_Db_Table_Row_Abstract
   */
  protected $_jobType;

  /**
   * @var boolean
   */
  protected $_wasIdle = false;

  /**
   * @var boolean
   */
  protected $_isComplete = false;

  /**
   * @var Zend_Log
   */
  protected $_log;

  /**
   * @var array
   */
  protected $_data;



  // Main

  /**
   * Constructor
   * 
   * @param Zend_Db_Table_Row_Abstract $job
   * @param Zend_Db_Table_Row_Abstract $jobType
   */
  public function __construct(Zend_Db_Table_Row_Abstract $job, $jobType = null)
  {
    if( !($job->getTable() instanceof Core_Model_DbTable_Jobs) ) {
      throw new Core_Model_Exception(sprintf('Job must belong to the ' .
        'Core_Model_DbTable_Jobs table, ' .
        'given %s', get_class($job->getTable())));
    }
    $this->_job = $job;

    if( null === $jobType ) {
      // Get job info if not provided
      $jobType = Engine_Api::_()->getDbtable('jobtypes', 'core')
        ->find($job->jobtype_id)
        ->current();
    }
    $this->_jobType = $jobType;

    // Load persistent data
    if( !empty($job->data) ) {
      $this->_data = Zend_Json::decode($job->data);
      if( !is_array($this->_data) ) {
        $this->_data = array();
      }
    }
  }

  /**
   * @param string $method
   * @param array $arguments
   * @throws Core_Model_Exception
   */
  public function __call($method, $arguments)
  {
    throw new Core_Model_Exception(sprintf('Unimplemented method %1$s in class %2$s', $method, get_class($this)));
  }

  /**
   * @return Zend_Db_Table_Row_Abstract
   */
  public function getJob()
  {
    return $this->_job;
  }

  /**
   * @return Zend_Db_Table_Row_Abstract
   */
  public function getJobType()
  {
    return $this->_jobType;
  }

  /**
   * Get our logger
   *
   * @return Zend_Log
   */
  public function getLog()
  {
    if( null === $this->_log ) {
      $log = new Zend_Log();
      $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/tasks.log'));
      $this->_log = $log;
    }
    return $this->_log;
  }

  /**
   * @param Zend_Log $log
   * @return Core_Plugin_Task_Abstract
   */
  public function setLog(Zend_Log $log)
  {
    $this->_log = $log;
    return $this;
  }
  
  
  
  // Progress

  /**
   * @return null|integer
   */
  public function getProgress()
  {
    if( is_array($this->_data) ) {
      if( isset($this->_data['progress']) ) {
        return $this->_data['progress'];
      }
    }
    return null;
  }

  /**
   * @return null|integer
   */
  public function getTotal()
  {
    if( is_array($this->_data) ) {
      if( isset($this->_data['total']) ) {
        return $this->_data['total'];
      }
    }
    return null;
  }

  /**
   * @return boolean
   */
  public function wasIdle()
  {
    return $this->_wasIdle;
  }

  /**
   * @param boolean $flag
   * @return Core_Plugin_Task_Abstract
   */
  protected function _setWasIdle($flag = true)
  {
    $this->_wasIdle = (bool) $flag;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isComplete()
  {
    return (bool) $this->_isComplete;
  }

  /**
   * @param boolean $flag
   * @return Core_Plugin_Task_Abstract
   */
  protected function _setIsComplete($flag)
  {
    $this->_isComplete = (bool) $flag;
    $this->_job->progress = 1;
    //if( $flag ) {
    //  $this->_data = null;
    //}
    return $this;
  }

  /**
   * Add a message to the job log
   *
   * @param string $message
   * @return Core_Plugin_Job_Abstract
   */
  protected function _addMessage($message)
  {
    $this->_job->messages .= $message . PHP_EOL;
    return $this;
  }

  /**
   * Set the job state, add a message
   * 
   * @param string $state
   * @param string $message
   * @param boolean $doSave
   * @return Core_Plugin_Job_Abstract
   */
  protected function _setState($state, $message = null, $doSave = true)
  {
    $this->_job->state = $state;
    if( in_array($state, array('failed', 'completed', 'cancelled')) ) {
      $this->_job->is_complete = true;
      $this->_job->completion_date = new Zend_Db_Expr('NOW()');
      $this->_setIsComplete(true);
    } else if( in_array($state, array('pending', 'active', 'sleeping')) ) {
      $this->_job->is_complete = false;
      $this->_job->completion_date = new Zend_Db_Expr('NULL');
    }
    if( $message ) {
      $this->_job->messages .= $message . PHP_EOL;
    }
    if( $doSave ) {
      $this->_job->save();
    }
    return $this;
  }



  // Data


  /**
   * @param array $data
   * @return Core_Plugin_Task_Abstract
   */
  public function setPersistentData($data)
  {
    $this->_data = $data;
    return $this;
  }

  /**
   * @return null|array
   */
  public function getPersistentData()
  {
    return $this->_data;
  }

  /**
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function getParam($key, $default = null)
  {
    if( is_array($this->_data) && isset($this->_data[$key]) ) {
      return $this->_data[$key];
    } else {
      return $default;
    }
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return Core_Plugin_Job_Abstract
   */
  public function setParam($key, $value)
  {
    if( !is_array($this->_data) ) {
      $this->_data = array();
    }
    $this->_data[$key] = $value;
    return $this;
  }



  // Execution

  /**
   * @return Core_Plugin_Job_Abstract
   */
  public function execute()
  {
    $this->_execute();

    // Save persistent data
    if( is_array($this->_data) && !empty($this->_data) ) {
      $this->_job->data = Zend_Json::encode($this->_data);
    } else {
      $this->_job->data = '';
    }

    // Update progress
    if( $this->isComplete() ) {
      $this->_job->progress = 1;
    } else if( $this->getTotal() > 0 ) {
      $this->_job->progress = ( $this->getProgress() / $this->getTotal() );
    }

    return $this;
  }

  abstract protected function _execute();
}