<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Settings.php 8147 2011-01-06 00:09:41Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Tasks_Settings extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this
      ->setTitle('Task Scheduler Settings')
      ->setDescription('CORE_FORM_ADMIN_SETTINGS_TASKS_DESCRIPTION')
      ;

    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    
    // Element: mode
    $multiOptions = array();
    
    $multiOptions['asset'] = 'Javascript';

    if( extension_loaded('curl') ) {
      $multiOptions['curl'] = 'cURL';
    }

    if( function_exists('fsockopen') ) {
      $multiOptions['socket'] = 'Socket';
    }
    
    //if( strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ) {
      $multiOptions['cron'] = 'Cron';
    //}

//    if( function_exists('exec') ) {
//      exec('php -v', $output, $return);
//      if( $return <= 0 ) {
//        $multiOptions['exec'] = 'CLI';
//      }
//    }

    if( function_exists('pcntl_fork') ) {
      $multiOptions['fork'] = 'Fork';
    }

    $description = null;
    if( isset($multiOptions['cron']) ) {
      $description = 'Cron requires setup in crontab or the Windows task scheduler';
    }
    
    $this->addElement('Select', 'mode', array(
      'label' => 'Trigger Method',
      'description' => $description,
      'multiOptions' => $multiOptions,
    ));

    // Element: key
    $this->addElement('Text', 'key', array(
      'label' => 'Trigger Access Key',
      'description' => 'Used to prevent unauthorized running of the task scheduler.',
    ));

    // Element: interval
    $this->addElement('Text', 'interval', array(
      'label' => 'Trigger Interval',
      'description' => 'The minimum time between running tasks, in seconds.',
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(0)),
      ),
    ));

    // Element: processes
    $this->addElement('Text', 'processes', array(
      'label' => 'Concurrent Processes',
      'description' => 'The maximum number of concurrent processes running tasks that are allowed.',
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(0)),
      ),
    ));

    // Element: count
    $this->addElement('Text', 'count', array(
      'label' => 'Tasks Run per Request',
      'description' => 'The maximum number of tasks that are run during each request. If a task is determined to have done nothing, it may not count towards this number.',
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(0)),
      ),
    ));

    // Element: time
    $this->addElement('Text', 'time', array(
      'label' => 'Time per Request',
      'description' => 'The maximum time allowed per request. This number will be automatically scaled if ini_get() can read max_execution_time from php.ini.',
      'validators' => array(
        array('Int', true),
        new Engine_Validate_AtLeast(30),
      ),
    ));

    // Element: timeout
    $this->addElement('Text', 'timeout', array(
      'label' => 'Process Timeout',
      'description' => 'The maximum time before a task is considered to have died. The task will then be reset and freed to execute again.',
      'validators' => array(
        array('Int', true),
        new Engine_Validate_AtLeast(60),
      ),
    ));

    // Element: jobs
    $this->addElement('Text', 'jobs', array(
      'label' => 'Concurrent Jobs',
      'description' => 'The maximum number of concurrently running jobs. This setting is limited by "Concurrent Processes"',
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(0)),
      ),
    ));
    
    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}