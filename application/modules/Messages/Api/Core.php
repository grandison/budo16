<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Messages_Api_Core extends Core_Api_Abstract
{
  public function getUnreadMessageCount(Core_Model_Item_Abstract $user)
  {
    $identity = $user->getIdentity();

    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $select = Engine_Api::_()->getDbtable('recipients', 'messages')->select()
      ->setIntegrityCheck(false)
      ->from($rName, new Zend_Db_Expr('COUNT(conversation_id) AS unread'))
      ->where($rName.'.user_id = ?', $identity)
      ->where($rName.'.inbox_deleted = ?', 0)
      ->where($rName.'.inbox_read = ?', 0);

    $data = Engine_Api::_()->getDbtable('recipients', 'messages')->fetchRow($select);
    return (int) $data->unread;
  }
}

/*
return;

class Messages_Api_CoreBak extends Core_Api_Abstract
{
  // Properties

  protected $_recipients = array();

  protected $_messages = array();

  protected $_conversations = array();



  // Messages

  public function getMessages(Core_Model_Item_Abstract $user, $type = 'inbox')
  {
    $method = 'getMessages'.ucfirst($type);
    return $this->$method($user);
  }

  public function getUnreadMessageCount(Core_Model_Item_Abstract $user)
  {
    $identity = $user->getIdentity();

    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $select = Engine_Api::_()->getDbtable('recipients', 'messages')->select()
      ->setIntegrityCheck(false)
      ->from($rName, new Zend_Db_Expr('COUNT(conversation_id) AS unread'))
      ->where($rName.'.user_id = ?', $identity)
      ->where($rName.'.inbox_deleted = ?', 0)
      ->where($rName.'.inbox_read = ?', 0);

    $data = Engine_Api::_()->getDbtable('recipients', 'messages')->fetchRow($select);
    return (int) $data->unread;
  }


  // Select

  public function getMessagesInbox(Core_Model_Item_Abstract $user)
  {
    return Zend_Paginator::factory($this->getMessagesSelectInbox($user));
  }

  public function getMessagesOutbox(Core_Model_Item_Abstract $user)
  {
    return Zend_Paginator::factory($this->getMessagesSelectOutbox($user));
  }

  public function getMessagesSelectInbox(Core_Model_Item_Abstract $user)
  {
    $identity = $user->getIdentity();

    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = Engine_Api::_()->getDbtable('conversations', 'messages')->info('name');
    $mName = Engine_Api::_()->getDbtable('messages', 'messages')->info('name');

    $select = Engine_Api::_()->getDbtable('recipients', 'messages')->select()
      ->setIntegrityCheck(false)
      ->from($rName, array('inbox_read', 'inbox_updated'))
      ->joinLeft($mName, $rName.'.conversation_id = '.$mName.'.conversation_id')
      ->joinLeft($cName, $rName.'.conversation_id = '.$cName.'.conversation_id')
      ->where($rName.'.user_id = ?', $identity)
      ->where($rName.'.inbox_message_id = '.$mName.'.message_id')
      ->where($rName.'.inbox_deleted = ?', 0)
      ->order(new Zend_Db_Expr('inbox_updated DESC'));

    return $select;
  }

  public function getMessagesSelectOutbox(Core_Model_Item_Abstract $user)
  {
    $identity = $user->getIdentity();

    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = Engine_Api::_()->getDbtable('conversations', 'messages')->info('name');
    $mName = Engine_Api::_()->getDbtable('messages', 'messages')->info('name');

    $select = Engine_Api::_()->getDbtable('recipients', 'messages')->select()
      ->setIntegrityCheck(false)
      ->from($rName)
      ->joinLeft($mName, $rName.'.conversation_id = '.$mName.'.conversation_id')
      ->joinLeft($cName, $rName.'.conversation_id = '.$cName.'.conversation_id')
      ->where($rName.'.user_id = ?', $identity)
      ->where($rName.'.outbox_message_id = '.$mName.'.message_id')
      ->where($rName.'.outbox_deleted = ?', 0)
      ->order(new Zend_Db_Expr('outbox_updated DESC'));

    return $select;
  }


  // Send

  public function sendMessage(Core_Model_Item_Abstract $user, $recipients, $title, $body, $attachment = null)
  {
    // Sanity check recipients
    $recipients = (array) $recipients;
    if( !is_array($recipients) || empty($recipients) )
    {
      throw new Messages_Model_Exception("A message must have recipients");
    }

    // Transaction
    $db = Engine_Api::_()->getDbtable('recipients', 'messages')->getAdapter();
    $db->beginTransaction();

    try
    {
      // Create conversation
      $conversation_id = Engine_Api::_()->getDbtable('conversations', 'messages')->insert(array(
        'recipients' => count($recipients),
        'modified' => date('Y-m-d H:i:s'),
        'locked' => 0
      ));

      // Create message
      $message_id = Engine_Api::_()->getDbtable('messages', 'messages')->insert(array(
        'conversation_id' => $conversation_id,
        'user_id' => $user->getIdentity(),
        'title' => $title,
        'body' => $body,
        'date' => date('Y-m-d H:i:s'),
        'attachment_type' => ( $attachment ? $attachment->getType() : '' ),
        'attachment_id' => ( $attachment ? $attachment->getIdentity() : 0 ),
      ));

      // Create sender outbox
      Engine_Api::_()->getDbtable('recipients', 'messages')->insert(array(
        'user_id' => $user->getIdentity(),
        'conversation_id' => $conversation_id,
        'outbox_message_id' => $message_id,
        'outbox_updated' => date('Y-m-d H:i:s'),
        'outbox_deleted' => 0,
        'inbox_deleted' => 1,
        'inbox_read' => 1
      ));

      // Create recipients inbox
      foreach( $recipients as $recipient_id )
      {
        Engine_Api::_()->getDbtable('recipients', 'messages')->insert(array(
          'user_id' => $recipient_id,
          'conversation_id' => $conversation_id,
          'inbox_message_id' => $message_id,
          'inbox_updated' => date('Y-m-d H:i:s'),
          'inbox_deleted' => 0,
          'inbox_read' => 0,
          'outbox_message_id' => 0,
          'outbox_deleted' => 1,
        ));
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $conversation_id;
  }

  public function replyMessage(Core_Model_Item_Abstract $user, $conversation, $body, $attachment = null)
  {
    // Transaction
    $db = Engine_Api::_()->getDbtable('recipients', 'messages')->getAdapter();
    $db->beginTransaction();

    // Make sure user is part of convo
    $recipients = $this->getConversationRecipientsInfo($conversation);
    $included = false;
    foreach( $recipients as $recipient )
    {
      if( $recipient->user_id == $user->getIdentity() )
      {
        $included = true;
      }
    }

    if( !$included )
    {
      throw new Messages_Model_Exception('Not part of convo');
    }

    // Get the original message title and add Re
    $messages = $this->getConversationMessages($user, $conversation);
    $title = 'Re: '.$messages->current()->title;

    try
    {
      // Update conversation
      Engine_Api::_()->getDbtable('conversations', 'messages')->update(array(
        'modified' => date('Y-m-d H:i:s')
      ), array(
        'conversation_id = ?' => $conversation
      ));

      // Insert message
      $message_id = Engine_Api::_()->getDbtable('messages', 'messages')->insert(array(
        'conversation_id' => $conversation,
        'user_id' => $user->getIdentity(),
        'title' => $title,
        'body' => $body,
        'date' => date('Y-m-d H:i:s'),
        'attachment_type' => ( $attachment ? $attachment->getType() : '' ),
        'attachment_id' => ( $attachment ? $attachment->getIdentity() : 0 ),
      ));

      // Update sender's outbox
      Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
        'outbox_message_id' => $message_id,
        'outbox_updated' => date('Y-m-d H:i:s'),
        'outbox_deleted' => 0
      ), array(
        'user_id = ?' => $user->getIdentity(),
        'conversation_id = ?' => $conversation
      ));

      // Update recipients' inbox
      Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
        'inbox_message_id' => $message_id,
        'inbox_updated' => date('Y-m-d H:i:s'),
        'inbox_deleted' => 0,
        'inbox_read' => 0
      ), array(
        'user_id != ?' => $user->getIdentity(),
        'conversation_id = ?' => $conversation
      ));

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function setMessageRead(Core_Model_Item_Abstract $user, $conversation)
  {
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'inbox_read' => 1
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'conversation_id = ?' => $conversation
    ));

    return $this;
  }



  // Conversations

  public function getConversationInfo($conversation)
  {
    if( !isset($this->_conversations[$conversation]) )
    {
      $table = Engine_Api::_()->getDbtable('conversations', 'messages');
      $select = $table->select()
        ->where('conversation_id = ?', $conversation)
        ->limit(1);
      $this->_conversations[$conversation] = $table->fetchRow($select);
    }

    return $this->_conversations[$conversation];
  }

  public function getConversationRecipients($conversation)
  {
    $info = $this->getConversationRecipientsInfo($conversation);
    $ids = array();
    foreach( $info as $row )
    {
      $ids[] = $row->user_id;
    }
    return $this->api()->user()->getUserMulti($ids);
  }

  public function getConversationRecipientsInfo($conversation)
  {
    if( !isset($this->_recipients[$conversation]) )
    {
      $table = Engine_Api::_()->getDbtable('recipients', 'messages');
      $select = $table->select()
        ->where('conversation_id = ?', $conversation);
      $this->_recipients[$conversation] = $table->fetchAll($select);
    }

    return $this->_recipients[$conversation];
  }

  public function getConversationMessages(Core_Model_Item_Abstract $user, $conversation)
  {
    if( !isset($this->_messages[$conversation]) )
    {
      // Check that the user belongs to the conversation
      $recipients = $this->getConversationRecipientsInfo($conversation);
      if( !$recipients->getRowMatching('user_id', $user->getIdentity()) )
      {
        throw new Messages_Model_Exception("specified user is not in the conversation");
      }

      // Get the messages
      $table = Engine_Api::_()->getDbtable('messages', 'messages');
      $select = $table->select()
        ->where('conversation_id = ?', $conversation)
        ->order('message_id');

      $this->_messages[$conversation] = $table->fetchAll($select);
    }

    return $this->_messages[$conversation];
  }
}

*/