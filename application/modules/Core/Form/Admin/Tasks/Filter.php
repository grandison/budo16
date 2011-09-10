<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Filter.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Tasks_Filter extends Engine_Form
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
    $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');
    $modulesTable = Engine_Api::_()->getDbtable('modules', 'core');
    $select = new Zend_Db_Select($tasksTable->getAdapter());
    $modules = $select
      ->distinct()
      ->from($tasksTable->info('name'), 'module')
      ->joinLeft($modulesTable->info('name'), 'module=name', array('title'))
      ->where($modulesTable->info('name') . '.enabled = ?', 1)
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
    
    // Element: order
    $this->addElement('Select', 'order', array(
      'label' => 'Order',
      'multiOptions' => array(
        'task_id' => 'ID',
        'title' => 'Name',
        'timeout' => 'Timeout',
        'module' => 'Module',
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