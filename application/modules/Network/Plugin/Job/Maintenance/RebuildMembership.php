<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RebuildMembership.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_Plugin_Job_Maintenance_RebuildMembership extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    // Prepare tables
    $userTable = Engine_Api::_()->getItemTable('user');
    $valuesTable = Engine_Api::_()->fields()->getTable('user', 'values');
    $networkTable = Engine_Api::_()->getItemTable('network');
    $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');


    // Prepare
    $position   = $this->getParam('position', 0);
    $progress   = $this->getParam('progress', 0);
    $total      = $this->getParam('total');
    $limit      = $this->getParam('limit', 50);
    $isComplete = false;
    $break      = false;


    // Populate total
    if( null === $total ) {
      $total = $userTable->select()
        ->from($userTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
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



    // Get auto assignment networks
    $assignmentNetworks = array();
    foreach( $networkTable->fetchAll(array('assignment = ?' => 1)) as $network ) {
      $assignmentNetworks[] = $network;
    }



    // Execute
    $break = false;
    $count = 0;

    while( !$break && $count <= $limit ) {

      $user = $userTable->fetchRow($userTable->select()
        ->where('user_id >= ?', (int) $position + 1)->order('user_id ASC')->limit(1));

      if( !$user ) {
        $break = true;
        $isComplete = true;
      } else {
        $position = $user->getIdentity();
        $count++;
        $progress++;

        $values = $valuesTable->getValues($user);

        if( null !== $values ) {
          foreach( $assignmentNetworks as $assignmentNetwork ) {
            $assignmentNetwork->recalculate($user, $values);
          }

        }

        $valuesTable->clearValues();
        unset($user);
      }

    }


    // Cleanup
    $this->setParam('position', $position);
    $this->setParam('progress', $progress);
    $this->_setIsComplete($isComplete);
    if( $count <= 0 ) {
      $this->_setWasIdle();
    }
  }
}