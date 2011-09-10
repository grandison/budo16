<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Mail.php 7676 2010-10-20 22:00:38Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Task_Mail extends Core_Plugin_Task_Abstract
{
  protected $_max;

  protected $_count;

  protected $_break;

  protected $_offset;

  public function getTotal()
  {
    $table = Engine_Api::_()->getDbtable('mail', 'core');
    return $table->select()
      ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
      ->query()
      ->fetchColumn(0)
      ;
  }
  
  public function execute()
  {
    // Should we only process if queue is enabled?
    // Note: if mail gets disabled with mail in the queue, the remaining mail
    // would not get sent
    //if( Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.queue.enabled') ) {

    $this->_max = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_mail_count', 10);
    $this->_count = 0;
    $this->_break = false;
    $this->_offset = 0;
    
    $mailTable = Engine_Api::_()->getDbtable('mail', 'core');
    $db = $mailTable->getAdapter();

    // Loop until no mail left or count is reached
    while( $this->_count <= $this->_max && $this->_offset <= $this->_max && !$this->_break ) {
      // We should run each mail in a try-catch-transaction, not all at once
      $db->beginTransaction();
      try {
        $this->_processOne();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
      }
      $this->_offset++;
    }

    // We didn't do anything
    if( $this->_count <= 0 ) {
      $this->_setWasIdle();
    }
  }

  protected function _processOne()
  {
    // Select a single mail item
    $mailTable = Engine_Api::_()->getDbtable('mail', 'core');
    $mailSelect = $mailTable->select()->order('priority DESC')->order('mail_id ASC')->limit(1);
    $mailRow = $mailTable->fetchRow($mailSelect);
    if( null === $mailRow ) {
      $this->_break = true;
      return;
    } else if( $mailRow->type == 'zend' ) {
      $this->_processZend($mailRow);
    } else if( $mailRow->type == 'system' ) {
      $this->_processSystem($mailRow);
    } else {
      // wth?
    }
  }

  protected function _processZend($mailRow)
  {
    $mailObject = unserialize($mailRow->body);
    if( !($mailObject instanceof Zend_Mail) ) {
      throw new Engine_Exception('mail not Zend_Mail' . get_class($mailObject) . ' ' . gettype($mailObject));
    }
    
    // Single (all at once)
    if( count($mailObject->getRecipients()) > 0 ) {
      $this->_processZendSimple($mailRow, $mailObject);
    }

    // Multi
    else {
      $this->_processZendMulti($mailRow, $mailObject);
    }
  }

  protected function _processZendSimple($mailRow, $mailObject)
  {
    $mailApi = Engine_Api::_()->getApi('mail', 'core');
    $mailApi->sendRaw($mailObject);
    $this->_count += $mailRow->recipient_count;
    $mailRow->delete();
  }

  protected function _processZendMulti($mailRow, $mailObject)
  {
    // Get recipients. If number to send > than remaining send count, only select that many
    $limit = min($this->_max - $this->_count, $mailRow->recipient_count);

    // Nothing to do
    if( $limit <= 0 ) {
      if( $mailRow->recipient_count <= 0 ) {
        $mailRow->delete();
      }
      return;
    }

    // Get recipients data
    $mailRecipientsTable = Engine_Api::_()->getDbtable('mailRecipients', 'core');
    $mailRecipientsSelect = $mailRecipientsTable->select()
      ->where('mail_id = ?', $mailRow->mail_id)
      ->limit($limit);
    $recipientsRowset = $mailRecipientsTable->fetchAll($mailRecipientsSelect);

    $recipientIds = array();
    $emails[] = array();
    foreach( $recipientsRowset as $recipient ) {
      $recipientIds[] = $recipient->recipient_id;
      if( !empty($recipient->user_id) ) {
        $userObject = Engine_Api::_()->getItem('user', $recipient->user_id);
        $emails[] = $userObject->email;
      } else if( !empty($recipient->email) ) {
        $emails[] = $recipient->email;
      }
    }

    // Send each
    $mailApi = Engine_Api::_()->getApi('mail', 'core');
    foreach( $emails as $email ) {
      $mailObjectClone = clone $mailObject;
      $mailObjectClone->addTo($email);
      $mailApi->sendRaw($mailObjectClone);
      $this->_count++;
    }

    // Decrement/delete recipients and/or remove row
    // Amount sent was equal to remaining count
    if( $limit >= $mailRow->recipient_count ) {
      $mailRecipientsTable->delete(array(
        'mail_id = ?' => $mailRow->mail_id,
      ));

      $mailRow->delete();
    }
    // Amount sent was less than remaining count
    else
    {
      $mailRecipientsTable->delete(array(
        'mail_id = ?' => $mailRow->mail_id,
        'recipient_id IN(?)' => $recipientIds,
      ));

      $mailRow->recipient_count -= $limit;
      $mailRow->save();
    }
  }

  protected function _processSystem($mailRow)
  {
    // Get recipients. If number to send > than remaining send count, only select that many
    $limit = min($this->_max - $this->_count, $mailRow->recipient_count);

    $mailRecipientsTable = Engine_Api::_()->getDbtable('mailRecipients', 'core');
    $mailRecipientsSelect = $mailRecipientsTable->select()->where('mail_id = ?', $mailRow->mail_id)->limit($limit);
    $recipientsRowset = $mailRecipientsTable->fetchAll($mailRecipientsSelect);

    // Process recipients
    $recipientIds = array();
    $recipients = array();
    $uids = array();
    $emails = array();
    foreach( $recipientsRowset as $recipient ) {
      $recipientIds[] = $recipient->recipient_id;
      if( !empty($recipient->user_id) ) {
        $uids[] = $recipient->user_id;
      } else if( !empty($recipient->email) ) {
        $emails[] = $recipient->email;
      }
    }

    if( !empty($uids) ) {
      $recipientObjects = Engine_Api::_()->getItemTable('user')->find($uids);
      foreach( $recipientObjects as $recipientObject ) {
        $recipients[] = $recipientObject;
      }
    }

    if( !empty($emails) ) {
      $recipients += $emails;
    }
    

    // Send
    $params = unserialize($mailRow->body);
    
    $mailApi = Engine_Api::_()->getApi('mail', 'core');
    $mailApi->sendSystemRaw($recipients, $params['type'], $params['params']);

    // Decrement/delete recipients and/or remove row
    // Amount sent was equal to remaining count
    if( $limit >= $mailRow->recipient_count ) {
      $mailRecipientsTable->delete(array(
        'mail_id = ?' => $mailRow->mail_id,
      ));

      $mailRow->delete();
    }
    // Amount sent was less than remaining count
    else
    {
      $mailRecipientsTable->delete(array(
        'mail_id = ?' => $mailRow->mail_id,
        'recipient_id IN(?)' => $recipientIds,
      ));

      $mailRow->recipient_count -= $limit;
      $mailRow->save();
    }

    $this->_count += count($recipients);
  }
}