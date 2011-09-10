<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Conversation.php 8412 2011-02-08 23:24:28Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Messages_Model_Conversation extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'messages_general',
      'reset' => true,
      'action' => 'view',
      'id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getDescription()
  {
    // Get body of last message
    $messagesTable = Engine_Api::_()->getDbtable('messages', 'messages');
    $messagesSelect = $messagesTable->select()
      ->where('conversation_id = ?', $this->conversation_id)
      ->order('message_id DESC')
      ->limit(1)
      ;

    $message = $messagesTable->fetchRow($messagesSelect);
    if( null !== $message ) {
      return $message->body;
      // @todo generally should not use nl2br in non-view code
      //return nl2br(html_entity_decode($message->body));
    }

   return '';
  }

  public function hasResource()
  {
    return ( !empty($this->resource_type) && !empty($this->resource_id) );
  }

  public function getResource()
  {
    if( $this->hasResource() ) {
      return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
    }
  }
  
  public function hasRecipient(User_Model_User $user)
  {
    $table = Engine_Api::_()->getDbtable('recipients', 'messages');
    $select = $table->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('conversation_id = ?', $this->getIdentity())
      ->limit(1);
    $row = $table->fetchRow($select);
    return ( null !== $row );
  }

  public function getRecipients()
  {
    if( empty($this->store()->recipients) )
    {
      $ids = array();
      foreach( $this->getRecipientsInfo() as $row )
      {
        $ids[] = $row->user_id;
      }
      $this->store()->recipients = Engine_Api::_()->getItemMulti('user', $ids);
    }

    return $this->store()->recipients;
  }

  public function getRecipientInfo(User_Model_User $user)
  {
    return $this->getRecipientsInfo()->getRowMatching('user_id', $user->getIdentity());
  }
  
  public function getRecipientsInfo()
  {
    if( empty($this->store()->recipientsInfo) )
    {
      $table = Engine_Api::_()->getDbtable('recipients', 'messages');
      $select = $table->select()
        ->where('conversation_id = ?', $this->getIdentity());
      $this->store()->recipientsInfo = $table->fetchAll($select);
    }

    return $this->store()->recipientsInfo;
  }

  public function reply(User_Model_User $user, $body, $attachment)
  {
    $notInConvo = true;
    $recipients = $this->getRecipients();
    $recipientsInfo = $this->getRecipientsInfo();
    foreach( $recipients as $recipient )
    {
      if( $recipient->isSelf($user) )
      {
        $notInConvo = false;
      }
    }

    if( $notInConvo )
    {
      throw new Messages_Model_Exception('Specified user not in convo');
    }

    $convoTable = $this->getTable();
    $messagesTable = Engine_Api::_()->getItemTable('messages_message');

    // Update this
    $this->modified = date('Y-m-d H:i:s');
    $this->save();

    $title = 'Re: '.$this->getMessages($user)->current()->title;

    // Insert message
    $message = $messagesTable->createRow();
    $message->setFromArray(array(
      'conversation_id' => $this->getIdentity(),
      'user_id' => $user->getIdentity(),
      'title' => '', //$title,
      'body' => $body,
      'date' => date('Y-m-d H:i:s'),
      'attachment_type' => ( $attachment ? $attachment->getType() : '' ),
      'attachment_id' => ( $attachment ? $attachment->getIdentity() : 0 ),
    ));
    $message->save();

    // Update sender's outbox
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'outbox_message_id' => $message->getIdentity(),
      'outbox_updated' => date('Y-m-d H:i:s'),
      'outbox_deleted' => 0
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity(),
    ));

    // Update recipients' inbox
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'inbox_message_id' => $message->getIdentity(),
      'inbox_updated' => date('Y-m-d H:i:s'),
      'inbox_deleted' => 0,
      'inbox_read' => 0
    ), array(
      'user_id != ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity(),
    ));

    unset($this->store()->messages);

    return $message;
  }

  public function setAsRead(User_Model_User $user)
  {
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'inbox_read' => 1
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity()
    ));

    return $this;
  }

  public function getMessages(User_Model_User $user)
  {
    if( empty($this->store()->messages) )
    {
      if( !$this->hasRecipient($user) )
      {
        throw new Messages_Model_Exception('Specified user not in convo');
      }

      $table = Engine_Api::_()->getItemTable('messages_message');
      $select = $table->select()
        ->where('conversation_id = ?', $this->getIdentity())
        ->order('message_id');
        ;

      $this->store()->messages = $table->fetchAll($select);
    }

    return $this->store()->messages;
  }

  public function getInboxMessage(User_Model_User $user)
  {
    $recipient = $this->getRecipientInfo($user);
    if( empty($recipient->inbox_message_id) || $recipient->inbox_message_id == 'NULL' )
    {
      return null;
    }
    
    return Engine_Api::_()->getItem('messages_message', $recipient->inbox_message_id);
  }

  public function getOutboxMessage(User_Model_User $user)
  {
    $recipient = $this->getRecipientInfo($user);
    if( empty($recipient->outbox_message_id) || $recipient->outbox_message_id == 'NULL' )
    {
      return null;
    }
    
    return Engine_Api::_()->getItem('messages_message', $recipient->outbox_message_id);
  }
}