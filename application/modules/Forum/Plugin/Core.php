<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7899 2010-12-03 01:09:07Z steve $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Plugin_Core
{
  public function onStatistics($event)
  {
    $table  = Engine_Api::_()->getDbTable('topics', 'forum');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'forum topic');
  }
  
  public function onUserDeleteAfter($event)
  {
    $payload = $event->getPayload();
    $user_id = $payload['identity'];

    // Signatures
    $table = Engine_Api::_()->getDbTable('signatures', 'forum');
    $table->delete(array(
      'user_id = ?' => $user_id,
    ));

    // Moderators
    $table = Engine_Api::_()->getDbTable('listItems', 'forum');
    $select = $table->select()->where('child_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach( $rows as $row ) {
      $row->delete();
    }

    // Topics
    $table = Engine_Api::_()->getDbTable('topics', 'forum');
    $select = $table->select()->where('user_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach( $rows as $row ) {
      //$row->delete();
    }

    // Posts
    $table = Engine_Api::_()->getDbTable('posts', 'forum');
    $select = $table->select()->where('user_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach ($rows as $row)
    {
      //$row->delete();
    }

    // Topic views
    $table = Engine_Api::_()->getDbTable('topicviews', 'forum');
    $table->delete(array(
      'user_id = ?' => $user_id,
    ));
  }

  public function addActivity($event)
  {
    $payload = $event->getPayload();
    $object  = $payload['object'];

    // Only for object=forum
    if( $object instanceof Forum_Model_Topic ) {

      $content    = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');
      $allowTable = Engine_Api::_()->getDbtable('allow', 'authorization');

      // Registered
      // @todo adjust for member-level visibility
      /*
      if( $content == 'everyone' && $allowTable->isAllowed($object->getAuthorizationItem(), 'authorization_level', 'view') ) {
        $event->addResponse(array(
          'type' => 'registered',
          'identity' => 0
        ));
      }
      */

      // Everyone
      if( $content == 'everyone' && $allowTable->isAllowed($object->getAuthorizationItem(), 'everyone', 'view') ) {
        $event->addResponse(array(
          'type' => 'everyone',
          'identity' => 0
        ));
      }
    }
  }
}
