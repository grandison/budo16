<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Plugin_Task_Abstract
{
  /**
   * @var Zend_Db_Table_Row_Abstract
   */
  protected $_task;

  /**
   * @var boolean
   */
  protected $_wasIdle = false;

  /**
   * @var Zend_Log
   */
  protected $_log;



  // Main

  /**
   * Constructor
   *
   * @param Zend_Db_Table_Row_Abstract $task
   */
  public function __construct(Zend_Db_Table_Row_Abstract $task)
  {
    if( !($task->getTable() instanceof Core_Model_DbTable_Tasks) ) {
      throw new Core_Model_Exception(sprintf('Task must belong to the ' .
        'Core_Model_DbTable_Tasks table, ' .
        'given %s', get_class($task->getTable())));
    }
    $this->_task = $task;
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
  public function getTask()
  {
    return $this->_task;
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



  // Informational

  /**
   * @return null|integer
   */
  public function getTotal()
  {
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



  // Execution

  /**
   * @return Core_Plugin_Job_Abstract
   */
  abstract public function execute();
}