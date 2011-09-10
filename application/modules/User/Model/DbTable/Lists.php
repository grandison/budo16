<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Lists.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Model_DbTable_Lists extends Engine_Db_Table
{
  protected $_rowClass = 'User_Model_List';

  public function removeFriendFromLists(User_Model_User $resource, User_Model_User $user){
    // get viewer (the person removing the friend)
    $viewer = Engine_Api::_()->user()->getViewer();

    // get the friendship list + items the user owns
    $listTable = Engine_Api::_()->getItemTable('user_list');
    $this->view->lists = $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $viewer->getIdentity()));

    $listIds = array();
    foreach( $lists as $list ) {
      $listIds[] = $list->list_id;
    }

    // Build lists by user
    $listItems = array();
    $listsByUser = array();
    if( !empty($listIds) ) {
      $listItemTable = Engine_Api::_()->getItemTable('user_list_item');
      $listItemSelect = $listItemTable->select()
        ->where('list_id IN(?)', $listIds)
        ->where('child_id = ?', $resource->getIdentity());
      $listItems = $listItemTable->fetchAll($listItemSelect);
      foreach( $listItems as $listItem ) {
        $listItem->delete();
      }
    }
  }

  public function removeUserFromLists(User_Model_User $user){
    Engine_Api::_()->getItemTable('user_list_item')->delete(array(
      'child_id = ?' => $user->getIdentity()
    ));
  }

  public function removeUserLists(User_Model_User $user){
    Engine_Api::_()->getItemTable('user_list')->delete(array(
      'owner_id = ?' => $user->getIdentity()
    ));
  }
  
}