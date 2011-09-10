<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RebuildPrivacy.php 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Plugin_Job_Maintenance_RebuildPrivacy extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    // Prepare
    $position   = $this->getParam('position', 0);
    $progress   = $this->getParam('progress', 0);
    $total      = $this->getParam('total');
    $limit      = $this->getParam('limit', 100);
    $isComplete = false;
    $break      = false;


    // Prepare tables
    $tasksTable = Engine_Api::_()->getDbtable('tasks', 'core');
    $actionTable = Engine_Api::_()->getItemTable('activity_action');


    // Populate total
    if( null === $total ) {
      $total = $actionTable->select()
        ->from($actionTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
        ->query()
        ->fetchColumn(0)
        ;
      $this->setParam('total', $total);
      if( !$progress ) {
        $this->setParam('progress', 0);
      }
      if( !$position ) {
        $this->setParam('position', 0);
      }
    }

    // Complete if nothing to do
    if( $total <= 0 ) {
      $this->_setWasIdle();
      $this->_setIsComplete(true);
      return;
    }
    

    // Don't run yet if there are any rebuild privacy plugins running
    $rebuildPrivacyTaskCount = $tasksTable->select()
      ->from($tasksTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
      ->where('plugin != ?', 'Activity_Plugin_Task_Maintenance_RebuildPrivacy')
      ->where('category = ?', 'rebuild_privacy')
      ->where('state != ?', 'dormant')
      ->query()
      ->fetchColumn(0)
      ;

    if( $rebuildPrivacyTaskCount > 0 ) {
      $this->_setWasIdle();
      return;
    }

    
    // Execute
    $count = 0;
    
    while( !$break && $count <= $limit ) {

      $action = $actionTable->fetchRow($actionTable->select()
          ->where('action_id >= ?', (int) $position + 1)->order('action_id ASC')->limit(1));

      // Nothing left
      if( !$action ) {
        $break = true;
        $isComplete = true;
      }

      // Main
      else {
        $position = $action->getIdentity();
        $count++;
        $progress++;

        $actionTable->resetActivityBindings($action);

        unset($action);
      }
      
    }
    

    // Cleanup
    $this->setParam('position', $position);
    $this->setParam('progress', $progress);
    $this->_setIsComplete($isComplete);
  }
}