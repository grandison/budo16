<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: MessagesController.php 8941 2011-05-14 02:18:46Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Messages_MessagesController extends Core_Controller_Action_User
{
  protected $_form;

  public function init()
  {
    $this->_helper->requireUser();
    $this->_helper->requireAuth()->setAuthParams('messages', null, 'create');
  }
  
  public function inboxAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('messages_main');
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')
        ->getInboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $this->view->unread = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
  }

  public function outboxAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('messages_main');
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')->getOutboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $this->view->unread = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
  }

  public function viewAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('messages_main');
    
    $id = $this->_getParam('id');
    $viewer = Engine_Api::_()->user()->getViewer();

    // Get conversation info
    $this->view->conversation = $conversation = Engine_Api::_()->getItem('messages_conversation', $id);

    // Make sure the user is part of the conversation
    if( !$conversation || !$conversation->hasRecipient($viewer) ) {
      return $this->_forward('inbox');
    }

    // Check for resource
    if( !empty($conversation->resource_type) &&
        !empty($conversation->resource_id) ) {
      $resource = Engine_Api::_()->getItem($conversation->resource_type, $conversation->resource_id);
      if( !($resource instanceof Core_Model_Item_Abstract) ) {
        return $this->_forward('inbox');
      }
      $this->view->resource = $resource;
    }
    // Otherwise get recipients
    else {
      $this->view->recipients = $recipients = $conversation->getRecipients();
      
      $blocked = false;
      $blocker = "";

      // This is to check if the viewered blocked a member
      $viewer_blocked = false;
      $viewer_blocker = "";

      foreach($recipients as $recipient){
        if ($viewer->isBlockedBy($recipient)){
          $blocked = true;
          $blocker = $recipient;
        }
        elseif ($recipient->isBlockedBy($viewer)){
          $viewer_blocked = true;
          $viewer_blocker = $recipient;
        }
      }
      $this->view->blocked = $blocked;
      $this->view->blocker = $blocker;
      $this->view->viewer_blocked = $viewer_blocked;
      $this->view->viewer_blocker = $viewer_blocker;
    }


    // Can we reply?
    $this->view->locked = $conversation->locked;
    if( !$conversation->locked ) {
      
      // Assign the composing junk
      $composePartials = array();
      foreach( Zend_Registry::get('Engine_Manifest') as $data )
      {
        if( empty($data['composer']) ) continue;
        foreach( $data['composer'] as $type => $config )
        {
          $composePartials[] = $config['script'];
        }
      }
      $this->view->composePartials = $composePartials;


      // Process form
      $this->view->form = $form = new Messages_Form_Reply();
      if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
      {
        $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
        $db->beginTransaction();
        try
        {
          // Try attachment getting stuff
          $attachment = null;
          $attachmentData = $this->getRequest()->getParam('attachment');
          if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
            $type = $attachmentData['type'];
            $config = null;
            foreach( Zend_Registry::get('Engine_Manifest') as $data )
            {
              if( !empty($data['composer'][$type]) )
              {
                $config = $data['composer'][$type];
              }
            }
            if( $config ) {
              $plugin = Engine_Api::_()->loadClass($config['plugin']);
              $method = 'onAttach'.ucfirst($type);
              $attachment = $plugin->$method($attachmentData);

              $parent = $attachment->getParent();
              if($parent->getType() === 'user'){
                $attachment->search = 0;
                $attachment->save();
              }
              else {
                $parent->search = 0;
                $parent->save();
              }

            }
          }

          $values = $form->getValues();
          $values['conversation'] = (int) $id;

          $conversation->reply(
            $viewer,
            $values['body'],
            $attachment
          );
          /*
          Engine_Api::_()->messages()->replyMessage(
            $viewer,
            $values['conversation'],
            $values['body'],
            $attachment
          );
           *
           */

          // Send notifications
          foreach( $recipients as $user )
          {
            if( $user->getIdentity() == $viewer->getIdentity() )
            {
              continue;
            }
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
              $user,
              $viewer,
              $conversation,
              'message_new'
            );
          }

          // Increment messages counter
          Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

          $db->commit();
        }
        catch( Exception $e )
        {
          $db->rollBack();
          throw $e;
        }

        $form->populate(array('body' => ''));
        return $this->_helper->redirector->gotoRoute(array('action' => 'view', 'id' => $id));
      }
    }

    // Make sure to load the messages after posting :P
    $this->view->messages = $messages = $conversation->getMessages($viewer);

    $conversation->setAsRead($viewer);
  }

  public function composeAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('messages_main');

    // Make form
    $this->view->form = $form = new Messages_Form_Compose();
    //$form->setAction($this->view->url(array('to' => null, 'multi' => null)));

    // Get params
    $multi = $this->_getParam('multi');
    $to = $this->_getParam('to');
    $viewer = Engine_Api::_()->user()->getViewer();
    $toObject = null;

    // Build
    $isPopulated = false;
    if( !empty($to) && (empty($multi) || $multi == 'user') ) {
      $multi = null;
      // Prepopulate user
      $toUser = Engine_Api::_()->getItem('user', $to);
      if( $toUser instanceof User_Model_User &&
          (!$viewer->isBlockedBy($toUser) && !$toUser->isBlockedBy($viewer)) &&
          isset($toUser->user_id)) {
        $this->view->toObject = $toObject = $toUser;
        $form->toValues->setValue($toUser->getGuid());
        $isPopulated = true;
      } else {
        $multi = null;
        $to = null;
      }
    } else if( !empty($to) && !empty($multi) ) {
      // Prepopulate group/event/etc
      $item = Engine_Api::_()->getItem($multi, $to);
      // Potential point of failure if primary key column is something other
      // than $multi . '_id'
      $item_id = $multi . '_id';
      if( $item instanceof Core_Model_Item_Abstract &&
          isset($item->$item_id) && (
            $item->isOwner($viewer) ||
            $item->authorization()->isAllowed($viewer, 'edit')
          )) {
        $this->view->toObject = $toObject = $item;
        $form->toValues->setValue($item->getGuid());
        $isPopulated = true;
      } else {
        $multi = null;
        $to = null;
      }
    }
    $this->view->isPopulated = $isPopulated;

    // Build normal
    if( !$isPopulated ) {
      // Apparently this is using AJAX now?
//      $friends = $viewer->membership()->getMembers();
//      $data = array();
//      foreach( $friends as $friend ) {
//        $data[] = array(
//          'label' => $friend->getTitle(),
//          'id' => $friend->getIdentity(),
//          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
//        );
//      }
//      $this->view->friends = Zend_Json::encode($data);
    }
    
    // Assign the composing stuff
    $composePartials = array();
    foreach( Zend_Registry::get('Engine_Manifest') as $data )
    {
      if( empty($data['composer']) ) continue;
      foreach( $data['composer'] as $type => $config )
      {
        $composePartials[] = $config['script'];
      }
    }
    $this->view->composePartials = $composePartials;

    // Get config
    $this->view->maxRecipients = $maxRecipients = 10;


    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();

    try {
      // Try attachment getting stuff
      $attachment = null;
      $attachmentData = $this->getRequest()->getParam('attachment');
      if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
        $type = $attachmentData['type'];
        $config = null;
        foreach( Zend_Registry::get('Engine_Manifest') as $data )
        {
          if( !empty($data['composer'][$type]) )
          {
            $config = $data['composer'][$type];
          }
        }
        if( $config ) {
          $plugin = Engine_Api::_()->loadClass($config['plugin']);
          $method = 'onAttach'.ucfirst($type);
          $attachment = $plugin->$method($attachmentData);
          $parent = $attachment->getParent();
          if($parent->getType() === 'user'){
            $attachment->search = 0;
            $attachment->save();
          }
          else {
            $parent->search = 0;
            $parent->save();
          }
        }
      }
      
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = $form->getValues();

      // Prepopulated
      if( $toObject instanceof User_Model_User ) {
        $recipientsUsers = array($toObject);
        $recipients = $toObject;
      } else if( $toObject instanceof Core_Model_Item_Abstract &&
          method_exists($toObject, 'membership') ) {
        $recipientsUsers = $toObject->membership()->getMembers();
//        $recipients = array();
//        foreach( $recipientsUsers as $recipientsUser ) {
//          $recipients[] = $recipientsUser->getIdentity();
//        }
        $recipients = $toObject;
      }
      // Normal
      else {
        $recipients = preg_split('/[,. ]+/', $values['toValues']);
        // clean the recipients for repeating ids
        // this can happen if recipient is selected and then a friend list is selected
        $recipients = array_unique($recipients);
        // Slice down to 10
        $recipients = array_slice($recipients, 0, $maxRecipients);
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $recipients);
      }

      // Create conversation
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
        $viewer,
        $recipients,
        $values['title'],
        $values['body'],
        $attachment
      );

      // Send notifications
      foreach( $recipientsUsers as $user ) {
        if( $user->getIdentity() == $viewer->getIdentity() ) {
          continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
          $user,
          $viewer,
          $conversation,
          'message_new'
        );
      }

      // Increment messages counter
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      echo $e;die();
      throw $e;
    }
    
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')),
      'redirect' => $conversation->getHref(), //$this->getFrontController()->getRouter()->assemble(array('action' => 'inbox'))
    ));
  }
  
  public function successAction()
  {
    
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    
    $message_ids = $this->view->message_ids = $this->getRequest()->getParam('message_ids');
    if (!$this->getRequest()->isPost())
      return;
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->deleted_conversation_ids = array();
    
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      foreach (explode(',', $message_ids) as $message_id) {
        $recipients = Engine_Api::_()->getItem('messages_conversation', $message_id)->getRecipientsInfo();
        //$recipients = Engine_Api::_()->getApi('core', 'messages')->getConversationRecipientsInfo($message_id);
        foreach ($recipients as $r) {
          if ($viewer_id == $r->user_id) {
            $this->view->deleted_conversation_ids[] = $r->conversation_id;
            $r->inbox_deleted  = true;
            $r->outbox_deleted = true;
            $r->save();
          }
        }
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected messages have been deleted.');
    
    return $this->_forward('success' ,'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => Array($this->view->message)
    ));
  }
}