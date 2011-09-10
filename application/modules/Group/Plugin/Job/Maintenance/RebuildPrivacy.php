<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RebuildPrivacy.php 7926 2010-12-04 18:40:35Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Plugin_Job_Maintenance_RebuildPrivacy extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    // Start custom prepare
    $table = Engine_Api::_()->getItemTable('group');
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'officer', 'member', 'registered', 'everyone');
    $actions = array('view', 'comment', 'photo', 'event');
    // End custom prepare


    // Prepare
    $position   = $this->getParam('position', 0);
    $progress   = $this->getParam('progress', 0);
    $total      = $this->getParam('total');
    $limit      = $this->getParam('limit', 100);
    $isComplete = false;
    $break      = false;


    // Populate total
    if( null === $total ) {
      $total = $table->select()
        ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
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


    // Execute
    $count = 0;
    $primaryCol = array_shift($table->info('primary'));

    while( !$break && $count <= $limit ) {

      $item = $table->fetchRow($table->select()
          ->where('`' . $primaryCol . '` >= ?', (int) $position + 1)->order($primaryCol . ' ASC')->limit(1));

      // Nothing left
      if( !$item ) {
        $break = true;
        $isComplete = true;
      }

      // Main
      else {
        $position = $item->getIdentity();
        $count++;
        $progress++;

        // Get owner
        $itemOwner = null;
        try {
          $itemOwner = $item->getOwner('user');
          if( !($itemOwner instanceof User_Model_User) || !$itemOwner->getIdentity() ) {
            $itemOwner = null;
          }
        } catch( Exception $e ) {
          $itemOwner = null;
        }

        // Apply perms
        foreach( $actions as $action ) {

          // Get allowed options
          $options = array();
          if( $itemOwner ) {
            $options = (array) Engine_Api::_()->authorization()->getAdapter('levels')
              ->getAllowed($item->getType(), $itemOwner, 'auth_' . $action);
            $options = array_intersect($roles, $options);
          }

          if( empty($options) || !is_array($options) ) {
            $options = $roles;
          }

          // Get max allowed
          $maxAllowed = null;
          foreach( $roles as $roleString ) {
            // Hack for group officers
            if( $roleString == 'officer' ) {
              $role = $item->getOfficerList();
            } else {
              $role = $roleString;
            }
            
            if( 1 === $auth->isAllowed($item, $role, $action) ) {
              $maxAllowed = $role;
            }
          }

          if( !$maxAllowed ) {
            $maxAllowed = ( count($options) > 0 ? $options[count($options) - 1] : 'everyone' );
          }

          $maxAllowedIndex = array_search($maxAllowed, $roles);

          foreach( $roles as $i => $roleString ) {
            // Hack for group officers
            if( $roleString == 'officer' ) {
              $role = $item->getOfficerList();
            } else {
              $role = $roleString;
            }
            $auth->setAllowed($item, $role, $action, ($i <= $maxAllowedIndex) );
          }
        }

        // Cleanup
        unset($item);
        unset($itemOwner);
        unset($action);
        unset($options);
        unset($maxAllowed);
        unset($maxAllowedIndex);
        unset($i);
        unset($role);
        unset($roleString);
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
