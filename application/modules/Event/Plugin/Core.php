<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7296 2010-09-06 02:57:44Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Plugin_Core
{
  public function onStatistics($event)
  {
    $table = Engine_Api::_()->getItemTable('event');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'event');
  }

  public function onUserDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
      // Delete posts
      $postTable = Engine_Api::_()->getDbtable('posts', 'event');
      $postSelect = $postTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach( $postTable->fetchAll($postSelect) as $post ) {
        //$post->delete();
      }

      // Delete topics
      $topicTable = Engine_Api::_()->getDbtable('topics', 'event');
      $topicSelect = $topicTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach( $topicTable->fetchAll($topicSelect) as $topic ) {
        //$topic->delete();
      }

      // Delete photos
      $photoTable = Engine_Api::_()->getDbtable('photos', 'event');
      $photoSelect = $photoTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach( $photoTable->fetchAll($photoSelect) as $photo ) {
        $photo->delete();
      }
      
      // Delete memberships
      $membershipApi = Engine_Api::_()->getDbtable('membership', 'event');
      foreach( $membershipApi->getMembershipsOf($payload) as $event ) {
        $membershipApi->removeMember($event, $payload);
      }

      // Delete events
      $eventTable = Engine_Api::_()->getDbtable('events', 'event');
      $eventSelect = $eventTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach( $eventTable->fetchAll($eventSelect) as $event ) {
        $event->delete();
      }
    }
  }

  public function addActivity($event)
  {
    $payload = $event->getPayload();
    $subject = $payload['subject'];
    $object = $payload['object'];

    // Only for object=event
    if( $object instanceof Event_Model_Event &&
        Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view') ) {
      $event->addResponse(array(
        'type' => 'event',
        'identity' => $object->getIdentity()
      ));
    }
  }

  public function getActivity($event)
  {
    // Payload is viewer
    $payload = $event->getPayload();
    if( !($payload instanceof User_Model_User) ) {
      return;
    }

    // Get event memberships
    $data = Engine_Api::_()->getDbtable('membership', 'event')->getMembershipsOfIds($payload);
    if( !empty($data) && is_array($data) ) {
      $event->addResponse(array(
        'type' => 'event',
        'data' => $data,
      ));
    }
  }
}