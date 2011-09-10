<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: install.php 8371 2011-02-01 09:49:11Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Installer extends Engine_Package_Installer_Module
{
  function onInstall()
  {
    //
    // install content areas
    //
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    //INSERT INTO `engine4_core_content` (`content_id`, `page_id`, `type`, `name`, `parent_content_id`, `order`, `params`) VALUES

    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $page_id = $select->query()->fetchObject()->page_id;

    // group.profile-groups

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_content')
      ->where('page_id = ?', $page_id)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'group.profile-groups')
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'container')
        ->limit(1);
      $container_id = $select->query()->fetchObject()->content_id;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('parent_content_id = ?', $container_id)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle')
        ->limit(1);
      $middle_id = $select->query()->fetchObject()->content_id;

      // tab_id (tab container) may not always be there
      $select
        ->reset('where')
        ->where('type = ?', 'widget')
        ->where('name = ?', 'core.container-tabs')
        ->where('page_id = ?', $page_id)
        ->limit(1);
      $tab_id = $select->query()->fetchObject();
      if( $tab_id && @$tab_id->content_id ) {
          $tab_id = $tab_id->content_id;
      } else {
        $tab_id = null;
      }

      // tab on profile
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type'    => 'widget',
        'name'    => 'group.profile-groups',
        'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
        'order'   => 9,
        'params'  => '{"title":"Groups","titleCount":true}',
      ));

    }

    //
    // Group main page
    //
    // page


    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'group_profile_index')
      ->limit(1);
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {
      $db->insert('engine4_core_pages', array(
        'name' => 'group_profile_index',
        'displayname' => 'Group Profile',
        'title' => 'Group Profile',
        'description' => 'This is the profile for an group.',
        'custom' => 0,
        'provides' => 'subject=group',
      ));
      $page_id = $db->lastInsertId('engine4_core_pages');

      // containers
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $container_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $container_id,
        'order' => 3,
        'params' => '',
      ));
      $middle_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'left',
        'parent_content_id' => $container_id,
        'order' => 1,
        'params' => '',
      ));
      $left_id = $db->lastInsertId('engine4_core_content');

      // middle column
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.container-tabs',
        'parent_content_id' => $middle_id,
        'order' => 2,
        'params' => '{"max":"6"}',
      ));
      $tab_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-status',
        'parent_content_id' => $middle_id,
        'order' => 1,
        'params' => '',
      ));

      // left column
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-photo',
        'parent_content_id' => $left_id,
        'order' => 1,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-options',
        'parent_content_id' => $left_id,
        'order' => 2,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-info',
        'parent_content_id' => $left_id,
        'order' => 3,
        'params' => '',
      ));

      // tabs
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'activity.feed',
        'parent_content_id' => $tab_id,
        'order' => 1,
        'params' => '{"title":"Updates"}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-members',
        'parent_content_id' => $tab_id,
        'order' => 2,
        'params' => '{"title":"Members","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-photos',
        'parent_content_id' => $tab_id,
        'order' => 3,
        'params' => '{"title":"Photos","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-discussions',
        'parent_content_id' => $tab_id,
        'order' => 4,
        'params' => '{"title":"Discussions","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.profile-links',
        'parent_content_id' => $tab_id,
        'order' => 5,
        'params' => '{"title":"Links","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'group.profile-events',
        'parent_content_id' => $tab_id,
        'order' => 6,
        'params' => '{"title":"Events","titleCount":true}',
      ));
    }
    parent::onInstall();
  }
}
