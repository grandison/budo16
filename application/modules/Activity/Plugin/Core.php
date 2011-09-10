<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8546 2011-03-02 00:56:06Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Plugin_Core
{
  public function onItemDeleteBefore($event)
  {
    $item = $event->getPayload();

    Engine_Api::_()->getDbtable('actions', 'activity')->delete(array(
      'subject_type = ?' => $item->getType(),
      'subject_id = ?' => $item->getIdentity(),
    ));
    
    Engine_Api::_()->getDbtable('actions', 'activity')->delete(array(
      'object_type = ?' => $item->getType(),
      'object_id = ?' => $item->getIdentity(),
    ));

    if( $item instanceof User_Model_User ) {
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
        'user_id = ?' => $item->getIdentity(),
      ));
    }
    
    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
      'subject_type = ?' => $item->getType(),
      'subject_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
      'object_type = ?' => $item->getType(),
      'object_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('stream', 'activity')->delete(array(
      'subject_type = ?' => $item->getType(),
      'subject_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('stream', 'activity')->delete(array(
      'object_type = ?' => $item->getType(),
      'object_id = ?' => $item->getIdentity(),
    ));

    // Delete all attachments and parent posts
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $attachmentSelect = $attachmentTable->select()
      ->where('type = ?', $item->getType())
      ->where('id = ?', $item->getIdentity())
      ;

    $attachmentActionIds = array();
    foreach( $attachmentTable->fetchAll($attachmentSelect) as $attachmentRow )
    {
      $attachmentActionIds[] = $attachmentRow->action_id;
    }

    if( !empty($attachmentActionIds) ) {
      $attachmentTable->delete('action_id IN('.join(',', $attachmentActionIds).')');
      Engine_Api::_()->getDbtable('stream', 'activity')->delete('action_id IN('.join(',', $attachmentActionIds).')');
    }
    
  }
  
  public function getActivity($event)
  {
    // Detect viewer and subject
    $payload = $event->getPayload();
    $user = null;
    $subject = null;
    if( $payload instanceof User_Model_User ) {
      $user = $payload;
    } else if( is_array($payload) ) {
      if( isset($payload['for']) && $payload['for'] instanceof User_Model_User ) {
        $user = $payload['for'];
      }
      if( isset($payload['about']) && $payload['about'] instanceof Core_Model_Item_Abstract ) {
        $subject = $payload['about'];
      }
    }
    if( null === $user ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( $viewer->getIdentity() ) {
        $user = $viewer;
      }
    }
    if( null === $subject && Engine_Api::_()->core()->hasSubject() ) {
      $subject = Engine_Api::_()->core()->getSubject();
    }

    // Get feed settings
    $content = Engine_Api::_()->getApi('settings', 'core')
      ->getSetting('activity.content', 'everyone');

    // Owner
    if( $user ) {
      $event->addResponse(array(
        'type' => 'owner',
        'data' => $user->getIdentity()
      ));
    }

    // Parent
    if( $user ) {
      $event->addResponse(array(
        'type' => 'parent',
        'data' => $user->getIdentity()
      ));
    }

    // Members (friends)
    if( $user ) {
      $data = array();
      $data = $user->membership()->getMembershipsOfIds();

      if( !empty($data) ) {
        $event->addResponse(array(
          'type' => 'members',
          'data' => $data,
        ));
      }
    }

    // Network
    if( $user && ($subject || in_array($content, array('networks', 'everyone'))) ) {

      $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
      $ids = $networkTable->getMembershipsOfIds($user);

      foreach( $ids as $id ) {
        $event->addResponse(array(
          'type' => 'network',
          'data' => $id
        ));
      }
    }

    // Registered and Everyone
    if( $user && ($subject || $content == "everyone") ) {
      // Registered
      $event->addResponse(array(
        'type' => 'registered',
        'data' => 0
      ));

      // Everyone
      $event->addResponse(array(
        'type' => 'everyone',
        'data' => 0
      ));
    }
  }

  public function addActivity($event)
  {
    $payload = $event->getPayload();
    $subject = $payload['subject'];
    $object = $payload['object'];
    $content = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');

    // Get subject owner
    $subjectOwner = null;
    if( $subject instanceof User_Model_User ) {
      $subjectOwner = $subject;
    } else {
      try {
        $subjectOwner = $subject->getOwner('user');
      } catch( Exception $e ) {}
    }

    // Get object parent
    $objectParent = null;
    if( $object instanceof User_Model_User ) {
      $objectParent = $object;
    } else {
      try {
        $objectParent = $object->getParent('user');
      } catch( Exception $e ) {}
    }
    
    // Owner
    if( $subjectOwner instanceof User_Model_User ) {
      $event->addResponse(array(
        'type' => 'owner',
        'identity' => $subjectOwner->getIdentity()
      ));
    }

    // Parent
    if( $objectParent instanceof User_Model_User ) {
      $event->addResponse(array(
        'type' => 'parent',
        'identity' => $objectParent->getIdentity()
      ));
    }
    
    // Network
    if( in_array($content, array('everyone', 'networks')) ) {
      if ($object instanceof User_Model_User
          && Engine_Api::_()->authorization()->context->isAllowed($object, 'network', 'view') ) {
        $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
        $ids = $networkTable->getMembershipsOfIds($object);
        $ids = array_unique($ids);
        foreach( $ids as $id ) {
          $event->addResponse(array(
            'type' => 'network',
            'identity' => $id,
          ));
        }
      } elseif ($objectParent instanceof User_Model_User
          && Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_network', 'view') ) {
        $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
        $ids = $networkTable->getMembershipsOfIds($objectParent);
        $ids = array_unique($ids);
        foreach( $ids as $id ) {
          $event->addResponse(array(
            'type' => 'network',
            'identity' => $id,
          ));
        }
      }
    }

    // Members
    if( $object instanceof User_Model_User ) {
      if( Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view') ) {
        $event->addResponse(array(
          'type' => 'members',
          'identity' => $object->getIdentity()
        ));
      }
    } else if( $objectParent instanceof User_Model_User ) {
      // Note: technically we shouldn't do owner_member, however some things are using it
      if( Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_member', 'view') ||
          Engine_Api::_()->authorization()->context->isAllowed($object, 'parent_member', 'view') ) {
        $event->addResponse(array(
          'type' => 'members',
          'identity' => $objectParent->getIdentity()
        ));
      }
    }

    // Registered
    if( $content == 'everyone' &&
        Engine_Api::_()->authorization()->context->isAllowed($object, 'registered', 'view') ) {
      $event->addResponse(array(
        'type' => 'registered',
        'identity' => 0
      ));
    }

    
    // Everyone
    if( $content == 'everyone' &&
        Engine_Api::_()->authorization()->context->isAllowed($object, 'everyone', 'view') ) {
      $event->addResponse(array(
        'type' => 'everyone',
        'identity' => 0
      ));
    }
  }
}