<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Filter.php 8262 2011-01-19 00:54:11Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Job_Filter extends Engine_Form
{
  public function init()
  {
    // Form
    $this
      ->setMethod('GET')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->addAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ));


    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;



    // Element: moduleName
    $jobTypesTable = Engine_Api::_()->getDbtable('jobTypes', 'core');
    $modulesTable = Engine_Api::_()->getDbtable('modules', 'core');
    $select = new Zend_Db_Select($jobTypesTable->getAdapter());
    $modules = $select
      ->distinct()
      ->from($jobTypesTable->info('name'), 'module')
      ->joinLeft($modulesTable->info('name'), 'module=name', array('title'))
      ->where($modulesTable->info('name') . '.enabled = ?', 1)
      ->order('title')
      ->query()
      ->fetchAll()
      ;
    $multiOptions = array('' => '');
    foreach( $modules as $module ) {
      if( !empty($module['title']) ) {
        $multiOptions[$module['module']] = $module['title'];
      }
    }
    $this->addElement('Select', 'moduleName', array(
      'label' => 'Module',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: jobType
    $jobTypes = $jobTypesTable->select()
      ->from($jobTypesTable, array('jobtype_id', 'title', 'type'))
      ->order('title')
      ->query()
      ->fetchAll()
      ;
    $multiOptions = array('' => '');
    foreach( $jobTypes as $jobType ) {
      $multiOptions[$jobType['jobtype_id']] = $jobType['title'];
    }
    $this->addElement('Select', 'jobtype_id', array(
      'label' => 'Type',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
    
    // Element: order
    $this->addElement('Select', 'order', array(
      'label' => 'Order',
      'multiOptions' => array(
        'job_id' => 'ID',
        'jobtype_id' => 'Type',
        'state' => 'State',
        'progress' => 'Progress',
        'creation_date' => 'Queued Date',
        'started_date' => 'Started Date',
        'completion_date' => 'Completed Date',
        'priority' => 'Priority',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: direction
    $this->addElement('Select', 'direction', array(
      'label' => 'Direction',
      'multiOptions' => array(
        'ASC' => 'A-Z',
        'DESC' => 'Z-A',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: submit
    $this->addElement('Button', 'execute', array(
      'label' => 'Filter',
      'ignore' => true,
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
        array('HtmlTag2', array('tag' => 'div')),
      ),
    ));
  }
}