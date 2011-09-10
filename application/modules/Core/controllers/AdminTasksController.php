<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminTasksController.php 8432 2011-02-10 00:27:00Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminTasksController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Get navigation
    $this->view->navigation = $this->getNavigation();

    // Get task settings
    $this->view->taskSettings = Engine_Api::_()->getApi('settings', 'core')->core_tasks;
    
    // Make filter form
    $this->view->formFilter = $formFilter = new Core_Form_Admin_Tasks_Filter();

    // Process form
    $values = $this->_getAllParams();
    if( null === $this->_getParam('category') ) {
      $values['category'] = 'system';
    }
    if( !$formFilter->isValid($values) ) {
      $values = array();
    } else {
      $values = $formFilter->getValues();
    }
    $values = array_filter($values);
    $this->view->formFilterValues = $values;

    // Make select
    $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');
    $select = $tasksTable->select()
      ->where('module IN(?)', (array) Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames());

    // Make select - order
    if( empty($values['order']) ) {
      $values['order'] = 'task_id';
    }
    if( empty($values['direction']) ) {
      $values['direction'] = 'ASC';
    }
    $select->order($values['order'] . ' ' . $values['direction']);
    unset($values['order']);
    unset($values['direction']);

    // Make select - where
    if( isset($values['moduleName']) ) {
      $values['module'] = $values['moduleName'];
      unset($values['moduleName']);
    }
    foreach( $values as $key => $value ) {
      $select->where($tasksTable->getAdapter()->quoteIdentifier($key) . ' = ?', $value);
    }

    // Make paginator
    $this->view->tasks = $tasks = Zend_Paginator::factory($select);
    $tasks->setItemCountPerPage(25);
    $tasks->setCurrentPageNumber($this->_getParam('page'));

    // Get task progresses
    $taskProgress = array();
    /*
    foreach( $tasks as $task ) {
      try {
        $plugin = $tasksTable->getTaskPlugin($task);
        if( $plugin instanceof Core_Plugin_Task_Abstract ) {
          $total = $plugin->getTotal();
          $progress = $plugin->getProgress();
          if( $total || $progress ) {
            $taskProgress[$task->plugin]['progress'] = $plugin->getProgress();
            $taskProgress[$task->plugin]['total'] = $plugin->getTotal();
          }
        }
      } catch( Exception $e ) {
        
      }
    }
     * 
     */
    $this->view->taskProgress = $taskProgress;

    // Get task processes
    $this->view->processes = $processes = $tasksTable->getAdapter()->select()
      ->from('engine4_core_processes')
      ->query()
      ->fetchAll();

    $processIndex = array();
    foreach( $processes as $process ) {
      if( !empty($process['name']) && '' !== $process['name'] ) {
        $processIndex[$process['name']][] = $process;
      }
    }
    $this->view->processIndex = $processIndex;
  }

  public function jobsAction()
  {
    // Get navigation
    $this->view->navigation = $this->getNavigation();

    // Get filter form
    $this->view->formFilter = $formFilter = new Core_Form_Admin_Job_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array();
      //$formFilter->populate(array());
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'job_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    
    // Get jobs select
    $jobsTable = Engine_Api::_()->getDbtable('jobs', 'core');
    $select = $jobsTable->select()
      ;
    if( !empty($filterValues['moduleName']) ) {
      $jobTypesTable = Engine_Api::_()->getDbtable('jobTypes', 'core');
      $jobTypes = $jobTypesTable->select()
        ->from($jobTypesTable, 'jobtype_id')
        ->where('module = ?', $filterValues['moduleName'])
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN)
        ;
      $select->where('jobtype_id IN(?)', $jobTypes);
    }
    if( !empty($filterValues['jobtype_id']) ) {
      $select->where('jobtype_id = ?', $filterValues['jobtype_id']);
    }
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'DESC';
      }
      $select->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }


    // Get jobs paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Get job types
    $jobtypes = array();
    foreach( Engine_Api::_()->getDbtable('jobTypes', 'core')->fetchAll() as $jobtype ) {
      $jobtypes[$jobtype->jobtype_id] = $jobtype;
    }
    $this->view->jobtypes = $jobtypes;
  }

  public function jobAddAction()
  {
    // Get available types
    $jobTypeTable = Engine_Api::_()->getDbtable('jobTypes', 'core');
    $enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    $enabledJobTypesSelect = $jobTypeTable->select()
      //->from($jobTypeTable->info('name'), 'jobtype_id')
      ->where('enabled = ?', 1)
      ->where('module IN(?)', $enabledModules)
      ->where('form IS NOT NULL')
      ->where('form != ?', '')
      ;

    $this->view->enabledJobTypes = $enabledJobTypes = $jobTypeTable->fetchAll($enabledJobTypesSelect);

    // Check given type against available types
    $type = $this->_getParam('type');
    if( !$type ) {
      return;
    }
    $jobType = $enabledJobTypes->getRowMatching('type', $type);
    if( !$jobType ) {
      return;
    }
    $this->view->type = $type;


    // Show form
    $formClass = $jobType->form;
    Engine_Loader::loadClass($formClass);
    $this->view->form = $form = new $formClass();

    // Special case for generic
    if( $formClass == 'Core_Form_Admin_Job_Generic' ) {
      $form->setTitle($this->view->translate('Job: %1$s', $this->view->translate($jobType->title)));
    }

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();

    $jobTable = Engine_Api::_()->getDbtable('jobs', 'core');
    $db = $jobTable->getAdapter();

    try {

      $jobTable->addJob($jobType->type, $values);
      
    } catch( Exception $e ) {
      throw $e;
    }
    
    return $this->_helper->redirector->gotoRoute(array('action' => 'jobs', 'type' => null));
  }

  public function jobRetryAction()
  {
    // Get job
    if( !($jobId = $this->_getParam('job_id')) ||
        !($job = Engine_Api::_()->getDbtable('jobs', 'core')->find($jobId)->current()) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'jobs', 'job_id' => null));
    }

    // Retry job
    if( in_array($job->state, array('failed', 'cancelled')) ) {
      // @todo should we re-insert so it doesn't go to the top of the queue?
      $job->state = 'pending';
      $job->is_complete = false;
      $job->completion_date = new Zend_Db_Expr('NULL');
      $job->save();
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'jobs', 'job_id' => null));
  }

  public function jobCancelAction()
  {
    // Get job
    if( !($jobId = $this->_getParam('job_id')) ||
        !($job = Engine_Api::_()->getDbtable('jobs', 'core')->find($jobId)->current()) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'jobs', 'job_id' => null));
    }

    // Cancel job
    if( in_array($job->state, array('active', 'sleeping', 'pending')) ) {
      // @todo should we add a hook to let the job do something on cancel
      $job->state = 'cancelled';
      $job->is_complete = true; // meh
      $job->completion_date = new Zend_Db_Expr('NOW()');
      $job->save();
    }
    
    return $this->_helper->redirector->gotoRoute(array('action' => 'jobs', 'job_id' => null));
  }

  public function jobMessagesAction()
  {
    if( !($jobId = $this->_getParam('job_id')) ||
        !($job = Engine_Api::_()->getDbtable('jobs', 'core')->find($jobId)->current()) ) {
      return;
    }

    if( empty($job->messages) ) {
      return;
    }

    $this->view->messages = explode("\n", $job->messages);
  }

  public function processesAction()
  {
    // Get navigation
    $this->view->navigation = $this->getNavigation();

    // Get processes
    $table = Engine_Api::_()->getDbtable('processes', 'core');
    $select = $table->select();
    $this->view->processes = $table->fetchAll($select);
  }

  public function settingsAction()
  {
    // Get navigation
    $this->view->navigation = $this->getNavigation();

    // Make form
    $this->view->form = $form = new Core_Form_Admin_Tasks_Settings();

    // Get settings
    $current = Engine_Api::_()->getApi('settings', 'core')->core_tasks;
    
    // Make sure it's not set to curl if they don't have it
    if( !extension_loaded('curl') && $current['mode'] == 'curl' ) {
      Engine_Api::_()->getApi('settings', 'core')->core_tasks_mode = $current['mode'] = 'socket';
    }

    // Populate form
    $form->populate($current);
    
    if( $this->getRequest()->isPost() &&
        $form->isValid($this->getRequest()->getPost()) ) {
      $values = $form->getValues();
      if( $values['mode'] == 'cron' ) {
        $values['pid'] = '';
      }
      Engine_Api::_()->getApi('settings', 'core')->core_tasks = $values;
      $current = array_merge($current, $values);
    }

    // Add a notice for cron (wget)
    if( $current['mode'] == 'cron' ) {

      $minutes = ceil($current['interval'] / 60);

      // wget
      $executeUrl = ( _ENGINE_SSL ? 'https://' : 'http://' )
        . $_SERVER['HTTP_HOST']
        . $this->view->url(array('controller' => 'utility', 'action' => 'tasks'), 'default', true)
        . '?'
        . http_build_query(array('key' => $current['key'], 'notrigger' => 1))
        ;
      $executeUrl = escapeshellarg($executeUrl);
      $logFile = escapeshellarg(APPLICATION_PATH . '/temporary/log/tasks.log');
      $commandTemplate = 'echo $(wget -O - %1$s) 2>&1 >> %2$s';
      $command = sprintf($commandTemplate, $executeUrl, $logFile);
      $command = $this->view->escape($command);

      // cli
      $executeCliArgs = escapeshellarg(APPLICATION_PATH . '/application/cli.php')
        . ' ' . escapeshellarg('controller=utility,action=tasks,key=' . $current['key'] . ',notrigger=1');
      $command2Template = 'echo $(php -f %1$s) 2>&1 >> %2$s';
      $command2 = sprintf($command2Template, $executeCliArgs, $logFile);
      $command2 = $this->view->escape($command2);

      $form->getDecorator('Description')->setOption('escape', false);
      $minuteString = $this->view->translate(array('%1$s minute', '%1$s minutes', $minutes), $minutes);
      $form->addNotice($this->view->translate('Please set one of the the ' .
        'following commands to run in crontab or the windows task scheduler ' .
        'about every %1$s: <br /><br />' .
        'Requires wget command line utility (Linux-only):<br /> ' .
        '"%2$s"<br /><br /> ' .
        'Requires php command line utility (check that php-cli is installed and set up correctly):<br /> ' .
        '"%3$s"', $minuteString, $command, $command2));
    }
  }

  public function runAction()
  {
    if( $this->getRequest()->isPost() ) {
      $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');

      // Single mode
      if( null !== ($task_id = $this->_getParam('task_id')) && is_numeric($task_id) ) {
        $tasks = array($task_id);
      }

      // Multi mode
      else if( null !== ($tasks = $this->_getParam('selection')) && is_array($tasks) ) {
        $tasks = array_filter($tasks);
      }

      if( is_array($tasks) && !empty($tasks) ) {
        $taskObjects = $tasksTable->find($tasks);
        if( null !== $taskObjects ) {
          foreach( $taskObjects as $taskObject ) {
            $tasksTable->executeTask($taskObject);
          }
        }
      }
    }
    
    if( 'json' === $this->_helper->contextSwitch->getCurrentContext() ) {
      $this->view->status = true;
    } else {
      if( null !== ($return = $this->_getParam('return')) ) {
        return $this->_helper->redirector->gotoUrl($return, array('prependBase' => false));
      } else {
        return $this->_helper->redirector->gotoRoute(array('controller' => 'tasks'), 'admin_default', true);
      }
    }
  }

  public function resetAction()
  {
    if( $this->getRequest()->isPost() ) {
      $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');

      // Single mode
      if( null !== ($task_id = $this->_getParam('task_id')) && is_numeric($task_id) ) {
        $tasks = array($task_id);
      }

      // Multi mode
      else if( null !== ($tasks = $this->_getParam('selection')) && is_array($tasks) ) {
        $tasks = array_filter($tasks);
      }

      if( is_array($tasks) && !empty($tasks) ) {
        $taskObjects = $tasksTable->find($tasks);
        $tasksTable->reset($taskObjects);
      }
    }

    if( 'json' === $this->_helper->contextSwitch->getCurrentContext() ) {
      $this->view->status = true;
    } else {
      if( null !== ($return = $this->_getParam('return')) ) {
        return $this->_helper->redirector->gotoUrl($return, array('prependBase' => false));
      } else {
        return $this->_helper->redirector->gotoRoute(array('controller' => 'tasks'), 'admin_default', true);
      }
    }
  }
  
  public function getNavigation()
  {
    return new Zend_Navigation(array(
      array(
        'label' => 'Task Scheduler',
        'route' => 'admin_default',
        'module' => 'core',
        'controller' => 'tasks',
        'action' => 'index',
        'active' => ( $this->getRequest()->getActionName() == 'index' ),
      ),
      array(
        'label' => 'Job Queue',
        'route' => 'admin_default',
        'module' => 'core',
        'controller' => 'tasks',
        'action' => 'jobs',
        'active' => ( $this->getRequest()->getActionName() == 'jobs' ),
      ),
//      array(
//        'label' => 'Process List',
//        'route' => 'admin_default',
//        'module' => 'core',
//        'controller' => 'tasks',
//        'action' => 'processes',
//        'active' => ( $this->getRequest()->getActionName() == 'processes' ),
//      ),
      array(
        'label' => 'Task Scheduler Settings',
        'route' => 'admin_default',
        'module' => 'core',
        'controller' => 'tasks',
        'action' => 'settings',
        'active' => ( $this->getRequest()->getActionName() == 'settings' ),
      ),
      array(
        'label' => 'Task Scheduler Log',
        'route' => 'admin_default',
        'module' => 'core',
        'controller' => 'system',
        'action' => 'log',
        'params' => array(
          'file' => 'tasks',
        ),
      ),
    ));
  }
}