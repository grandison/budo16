<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RebuildPrivacy.php 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Plugin_Job_Maintenance_RebuildPrivacy extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    // Prepare tables
    $table = $userTable = Engine_Api::_()->getItemTable('user');


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
    $auth = Engine_Api::_()->authorization()->context;
    $availableLabels = array(
      'owner' => 'Only Me',
      'member' => 'Only My Friends',
      'network' => 'Only My Networks',
      'registered' => 'All Members',
      'everyone' => 'Everyone',
    );
    $roles = array('owner', 'member', 'network', 'registered', 'everyone');
    $count = 0;

    while( !$break && $count <= $limit ) {

      $user = $userTable->fetchRow($userTable->select()
        ->where('user_id >= ?', (int) $position + 1)->order('user_id ASC')->limit(1));

      // Nothing left
      if( !$user ) {
        $break = true;
        $isComplete = true;
      }

      // Main
      else {
        $position = $user->getIdentity();
        $count++;
        $progress++;

        $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_view');
        $view_options = array_intersect_key($availableLabels, array_flip($view_options));

        $comment_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $user, 'auth_comment');
        $comment_options = array_intersect_key($availableLabels, array_flip($comment_options));

        $maxViewRole = null;
        $maxCommentRole = null;
        foreach( $roles as $role ) {
          if( 1 === $auth->isAllowed($user, $role, 'view') ) {
            $maxViewRole = $role;
          }
          if( 1 === $auth->isAllowed($user, $role, 'comment') ) {
            $maxCommentRole = $role;
          }
        }

        if( !$maxViewRole ) $maxViewRole = ( count($view_options) > 0 ? $view_options[count($view_options) - 1] : 'everyone' );
        if( !$maxCommentRole ) $maxCommentRole = ( count($comment_options) > 0 ? $comment_options[count($comment_options) - 1] : 'everyone' );

        $privacy_max_role = array_search($maxViewRole, $roles);
        $comment_max_role = array_search($maxCommentRole, $roles);

        foreach( $roles as $i => $role ) {
          $auth->setAllowed($user, $role, 'view', ($i <= $privacy_max_role) );
          $auth->setAllowed($user, $role, 'comment', ($i <= $comment_max_role) );
        }

        unset($user);
        unset($view_options);
        unset($comment_options);
        unset($maxViewRole);
        unset($maxCommentRole);
        unset($privacy_max_role);
        unset($comment_max_role);
        unset($role);
        unset($i);
      }
      
    }


    // Cleanup
    $this->setParam('position', $position);
    $this->setParam('progress', $progress);
    $this->_setIsComplete($isComplete);
  }
}