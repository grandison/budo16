<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: install.php 8371 2011-02-01 09:49:11Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Installer extends Engine_Package_Installer_Module
{
  function onInstall()
  {
    $this->_addContentEventProfile();
    $this->_addContentMemberHome();
    $this->_addContentMemberProfile();
    parent::onInstall();
  }

  protected function _addContentMemberHome()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Get page id
    $page_id = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'user_index_home')
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    // event.home-upcoming

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $hasWidget = $select
      ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
      ->where('page_id = ?', $page_id)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'event.home-upcoming')
      ->query()
      ->fetchColumn()
      ;

    // Add it
    if( !$hasWidget ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $container_id = $select
        ->from('engine4_core_content', 'content_id')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'container')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $right_id = $select
        ->from('engine4_core_content', 'content_id')
        ->where('parent_content_id = ?', $container_id)
        ->where('type = ?', 'container')
        ->where('name = ?', 'right')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // insert
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type'    => 'widget',
        'name'    => 'event.profile-events',
        'parent_content_id' => $right_id,
        'order'   => 1,
        'params'  => '{"title":"Upcoming Events","titleCount":true}',
      ));
    }
  }

  protected function _addContentMemberProfile()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Get page id
    $page_id = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'user_profile_index')
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    // event.profile-events

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $hasProfileEvents = $select
      ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
      ->where('page_id = ?', $page_id)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'event.profile-events')
      ->query()
      ->fetchColumn()
      ;

    // Add it
    if( !$hasProfileEvents ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $container_id = $select
        ->from('engine4_core_content', 'content_id')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'container')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $middle_id = $select
        ->from('engine4_core_content', 'content_id')
        ->where('parent_content_id = ?', $container_id)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // tab_id (tab container) may not always be there
      $select = new Zend_Db_Select($db);
      $tab_id = $select
        ->from('engine4_core_content', 'content_id')
        ->where('type = ?', 'widget')
        ->where('name = ?', 'core.container-tabs')
        ->where('page_id = ?', $page_id)
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // insert
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type'    => 'widget',
        'name'    => 'event.profile-events',
        'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
        'order'   => 8,
        'params'  => '{"title":"Events","titleCount":true}',
      ));
    }
  }

  protected function _addContentEventProfile()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $hasWidget = $select
      ->from('engine4_core_pages', new Zend_Db_Expr('TRUE'))
      ->where('name = ?', 'event_profile_index')
      ->limit(1)
      ->query()
      ->fetchColumn()
      ;

    // Add it
    if( empty($hasWidget) ) {

      $db->insert('engine4_core_pages', array(
        'name' => 'event_profile_index',
        'displayname' => 'Event Profile',
        'title' => 'Event Profile',
        'description' => 'This is the profile for an event.',
        'custom' => 0,
        'provides' => 'subject=event',
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
        'name' => 'event.profile-status',
        'parent_content_id' => $middle_id,
        'order' => 1,
        'params' => '',
      ));

      // left column
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-photo',
        'parent_content_id' => $left_id,
        'order' => 1,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-options',
        'parent_content_id' => $left_id,
        'order' => 2,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-info',
        'parent_content_id' => $left_id,
        'order' => 3,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-rsvp',
        'parent_content_id' => $left_id,
        'order' => 4,
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
        'name' => 'event.profile-members',
        'parent_content_id' => $tab_id,
        'order' => 2,
        'params' => '{"title":"Guests","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-photos',
        'parent_content_id' => $tab_id,
        'order' => 3,
        'params' => '{"title":"Photos","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'event.profile-discussions',
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
    }
  }
}
