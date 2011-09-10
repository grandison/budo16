<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Jobs.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Task_Jobs extends Core_Plugin_Task_Abstract
{
  protected $_isShutdownRegistered;
  
  protected $_isExecuting;

  protected $_executingJob;

  protected $_executingJobType;
  
  public function getTotal()
  {
    $table = Engine_Api::_()->getDbtable('jobs', 'core');
    return $table->select()
      ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
      ->where('is_complete = ?', 0)
      ->query()
      ->fetchColumn(0)
      ;
  }

  public function execute()
  {
    // Get max time limit information
    $start = _ENGINE_REQUEST_START;
    $limit = Engine_Api::_()->getDbtable('tasks', 'core')->getParam('time', 120);
    $jobs = Engine_Api::_()->getDbtable('tasks', 'core')->getParam('jobs', 3);
    $eTime = $start + $limit;
    $count = 0;
    $offset = 0;
    
    // Run jobs
    $job = true;
    while( $count < $jobs &&
        ($cTime = time()) <= $eTime &&
        ($job = $this->_getNextJob($offset)) ) {
      // Execute
      $this->_executeJob($job);
      // Increment count
      $count++;
      // Increment offset if getNextJob might select it again
      if( !$job->is_complete ) {
        $offset++;
      }
    }

    // Log reason for loop cancel
    if( 'development' == APPLICATION_ENV ) {
      if( $cTime > $eTime ) {
        $this->getLog()->log(sprintf('Job Execution Loop Cancelled - Out of time: %d > %d',
            $cTime, $eTime), Zend_Log::DEBUG);
        //$this->getLog()->log(sprintf('Job Execution Loop Cancelled - Out of time: NOW(%d) > END(START(%d) + LIMIT(%d) = %d)',
        //    $cTime, $start, $limit, $eTime), Zend_Log::DEBUG);
      } else if( $count >= $jobs ) {
        $this->getLog()->log(sprintf('Job Execution Loop Cancelled - Limit reached: %d >= %d',
            $count, $jobs), Zend_Log::DEBUG);
      } else if( !$job ) {
        $this->getLog()->log('Job Execution Loop Cancelled - Nothing to do', Zend_Log::DEBUG);
      } else {
        $this->getLog()->log('Job Execution Loop Cancelled - Unknown', Zend_Log::DEBUG);
      }
    }
    
    // Clear shutdown
    $this->_isExecuting = false;

    // Set idle
    if( $count <= 0 ) {
      $this->_setWasIdle(true);
    }
  }



  // Utility

  protected function _executeJob(Zend_Db_Table_Row_Abstract $job)
  {
    // Get job info
    $jobTypeTable = Engine_Api::_()->getDbtable('jobtypes', 'core');
    $jobType = $jobTypeTable
      ->find($job->jobtype_id)
      ->current();
    
    // Prepare data
    $data = array();
    $where = array(
      'job_id = ?' => $job->job_id,
      'state = ?' => $job->state,
    );
    if( $job->state == 'pending' ) {
      $data['state'] = 'active';
      $data['started_date'] = new Zend_Db_Expr('NOW()');
      $data['modified_date'] = new Zend_Db_Expr('NOW()');
    } else if( $job->state == 'sleeping' ) {
      $data['state'] = 'active';
      $data['modified_date'] = new Zend_Db_Expr('NOW()');
    } else {
      // wth is this?
      $this->getLog()->log('Job Execution Duplicate: ' . $jobType->title . ' ' . $job->state, Zend_Log::NOTICE);
      return;
    }

    // Attempt lock
    $table = $job->getTable();
    $affected = $table->update($data, $where);
    if( 1 !== $affected ) {
      $this->getLog()->log('Job Execution Failed Lock: ' . $jobType->title, Zend_Log::NOTICE);
      return;
    }

    // Refresh
    $job->refresh();
    
    // Register fatal error handler
    if( !$this->_isShutdownRegistered ) {
      register_shutdown_function(array($this, 'handleShutdown'));
      $this->_isShutdownRegistered = true;
    }

    // Signal execution
    $this->_isExecuting = true;
    $this->_executingJob = $job;
    $this->_executingJobType = $jobType;

    // Log
    if( APPLICATION_ENV == 'development' ) {
      $this->getLog()->log('Job Execution Start: ' . $jobType->title, Zend_Log::NOTICE);
    }

    // Initialize
    $isComplete = true;
    $wasIdle = false;
    $messages = array();
    $progress = null;
    
    try {
      
      // Check job type
      if( !$jobType || !$jobType->plugin ) {
        throw new Engine_Exception(sprintf('Missing job type with ID "%1$d"', $job->jobtype_id));
      }

      // Get plugin
      $plugin = $jobTypeTable->getJobPlugin($job, $jobType);

      // Execute
      $plugin->execute();

      // Cleanup
      $isComplete = (bool) $plugin->isComplete();
      //$progress = $plugin->getProgress();
      $wasIdle = $plugin->wasIdle();

      // If job set itself to failed, it failed. Otherwise, job may have not
      // set a status
      if( $job->state == 'failed' || $job->state == 'cancelled' ) {
        $status = false;
      } else {
        $status = true;
      }

    } catch( Exception $e ) {
      $messages[] = $e->getMessage();
      $this->getLog()->log(sprintf('Job Execution Error: [%d] [%s] %s %s', $job->job_id, $jobType->type, $jobType->title, $e->__toString()), Zend_Log::ERR);
      $status = false;
    }
    
    // Log
    if( APPLICATION_ENV == 'development' ) {
      if( $status ) {
        $this->getLog()->log(sprintf('Job Execution Complete: [%d] [%s] %s', $job->job_id, $jobType->type, $jobType->title), Zend_Log::NOTICE);
      } else {
        $this->getLog()->log(sprintf('Job Execution Complete (with errors): [%d] [%s] %s', $job->job_id, $jobType->type, $jobType->title), Zend_Log::ERR);
      }
    }

    // Update job
    $job->messages .= ltrim(join("\n", $messages) . "\n", "\n");
    $job->modified_date = new Zend_Db_Expr('NOW()');
    if( !$isComplete ) {
      $job->is_complete = false;
      $job->state = 'sleeping';
    } else {
      $job->is_complete = true;
      $job->state = ( $status ? 'completed' : 'failed' );
      $job->completion_date = new Zend_Db_Expr('NOW()');
    }
    $job->save();

    // Cleanup
    $this->_executingJobType = null;
    $this->_executingJob = null;
    $this->_isExecuting = false;
  }

  protected function _getNextJob($offset = 0)
  {
    /*
    SELECT * FROM `engine4_core_jobs` WHERE is_complete = 0 &&
    state IN('pending', 'sleeping') && jobtype_id IN(1, 2, 3, 4)
    ORDER BY priority ASC, job_id ASC
     */
    $enabledJobTypes = Engine_Api::_()->getDbtable('jobTypes', 'core')->getEnabledJobTypeIdentities();
    $jobsTable = Engine_Api::_()->getDbtable('jobs', 'core');
    $select = $jobsTable
      ->select()
      ->where('is_complete = ?', 0)
      //->where('state IN(?)', array('pending', 'sleeping'))
      //->where('jobtype_id IN(?)', $enabledJobTypes)
      ->order('priority ASC')
      ->order('job_id ASC')
      ->limit(1, (int) $offset)
      ;

    return $jobsTable->fetchRow($select);
  }

  public function handleShutdown()
  {
    if( $this->_isExecuting &&
        $this->_executingJob instanceof Zend_Db_Table_Row_Abstract &&
        $this->_executingJob->getTable() instanceof Core_Model_DbTable_Jobs ) {

      // Get error
      $message = '';
      if( function_exists('error_get_last') ) {
        $message = error_get_last();
        $message = $message['type'] . ' ' . $message['message'] . ' ' . $message['file'] . ' ' . $message['line'];
      }
      
      // Log
      if( APPLICATION_ENV == 'development' ) {
        $title = '';
        if( $this->_executingJobType ) {
          $title = $this->_executingJobType->title;
        }
        $this->getLog()->log('Job Execution Failure: ' . $title . ' ' . $message, Zend_Log::ERR);
      }

      // Cleanup
      try {
        $job = $this->_executingJob;
        $job->state = 'failed';
        $job->is_complete = true;
        $job->completion_date = new Zend_Db_Expr('NOW()');
        $job->messages .= $message;
        $job->save();
      } catch( Exception $e ) {
        $this->getLog()->log('Job Cleanup Failure: ' . $e->__toString(), Zend_Log::ERR);
      }
      
      $this->_isExecuting = false;
      $this->_executingJob = null;
      $this->_executingJobType = null;
    }
  }
}
