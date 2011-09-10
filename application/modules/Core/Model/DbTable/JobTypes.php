<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: JobTypes.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_JobTypes extends Engine_Db_Table
{
  protected $_enabledJobTypeIdentities;
  
  public function getEnabledJobTypeIdentities()
  {
    if( null == $this->_enabledJobTypeIdentities ) {
      $enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
      $this->_enabledJobTypes = $this->select()
        ->from($this->info('name'), 'jobtype_id')
        ->where('enabled = ?', 1)
        ->where('module IN(?)', $enabledModules)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN)
        ;
    }
    
    return $this->_enabledJobTypeIdentities;
  }

  public function getLog()
  {
    return Engine_Api::_()->getDbtable('tasks', 'core')->getLog();
  }

  public function getJobPlugin($job, $jobType = null)
  {
    // Must be a row of jobs
    if( !is_object($job) ) {
      throw new Core_Model_Exception(sprintf('Must be given a row of ' .
        'Core_Model_DbTable_Jobs, given ' .
        '%s', gettype($job)));
    } else if( !($job->getTable() instanceof Core_Model_DbTable_Jobs) ) {
      throw new Core_Model_Exception(sprintf('Must be given a row of ' .
          'Core_Model_DbTable_Jobs, given ' .
          '%s', get_class($job)));
    }

    // Get job type if missing
    if( null === $jobType ) {
      $jobType = $this
        ->find($job->jobtype_id)
        ->current();
    }

    // Get plugin class
    $class = $jobType->plugin;

    // Load class
    Engine_Loader::loadClass($class);

    // Make sure is a subclass of Core_Plugin_Task_Abstract
    if( !is_subclass_of($class, 'Core_Plugin_Job_Abstract') ) {
      throw new Core_Model_Exception(sprintf('Job plugin %1$s should extend Core_Plugin_Job_Abstract', $class));
    }

    // Check for execute method?
    if( !method_exists($class, 'execute') ) {
      throw new Core_Model_Exception(sprintf('Job plugin %1$s does not have an execute method', $class));
    }

    // Get plugin object
    $plugin = new $class($job, $jobType);

    // Set the log
    $plugin->setLog($this->getLog());

    return $plugin;
  }
}