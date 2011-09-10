<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: EditController.php 8407 2011-02-07 23:13:09Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_EditController extends Core_Controller_Action_User
{
  public function init()
  {
    if( !Engine_Api::_()->core()->hasSubject() )
    {
      // Can specifiy custom id
      $id = $this->_getParam('id', null);
      $subject = null;
      if( null === $id )
      {
        $subject = $this->_helper->api()->user()->getViewer();
        $this->_helper->api()->core()->setSubject($subject);
      }
      else
      {
        $subject = $this->_helper->api()->user()->getUser($id);
        $this->_helper->api()->core()->setSubject($subject);
      }
    }
    
    if( !empty($id) ) {
      $params = array('params' => array('id' => $id));
    } else {
      $params = array();
    }
    // Set up navigation
    $this->view->navigation = $navigation = $this->_helper->api()
      ->getApi('menus', 'core')
      ->getNavigation('user_edit', array('params'=>array('id'=>$id)));

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject('user');
    $this->_helper->requireAuth()->setAuthParams(
      null,
      null,
      'edit'
    );
  }
  
  public function profileAction()
  {
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = $this->_helper->api()->user()->getViewer();

    // remove styles if users are not allowed to style page
    $style_perm = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $user->level_id, 'style');
    if(!$style_perm) $this->view->navigation->removePage(2);

    
    // General form w/o profile type
    $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
    $this->view->topLevelId = $topLevelId = 0;
    $this->view->topLevelValue = $topLevelValue = null;
    if( isset($aliasedFields['profile_type']) ) {
      $aliasedFieldValue = $aliasedFields['profile_type']->getValue($user);
      $topLevelId = $aliasedFields['profile_type']->field_id;
      $topLevelValue = ( is_object($aliasedFieldValue) ? $aliasedFieldValue->value : null );
      if( !$topLevelId || !$topLevelValue ) {
        $topLevelId = null;
        $topLevelValue = null;
      }
      $this->view->topLevelId = $topLevelId;
      $this->view->topLevelValue = $topLevelValue;
    }
    
    // Get form
    $form = $this->view->form = new Fields_Form_Standard(array(
      'item' => $this->_helper->api()->core()->getSubject(),
      'topLevelId' => $topLevelId,
      'topLevelValue' => $topLevelValue,
    ));
    //$form->generate();
    
    // Not posting
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $form->saveValues();

      // Update display name
      $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
      $user->setDisplayName($aliasValues);
      //$user->modified_date = date('Y-m-d H:i:s');
      $user->save();

      // update networks
      Engine_Api::_()->network()->recalculate($user);
      
      $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
    }
  }


  public function photoAction()
  {

    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = $this->_helper->api()->user()->getViewer();

    // remove styles if users are not allowed to style page
    $style_perm = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $user->level_id, 'style');
    if(!$style_perm) $this->view->navigation->removePage(2);

    // Get form
    $this->view->form = $form = new User_Form_Edit_Photo();

    if (empty($user->photo_id)) {
      $form->removeElement('remove');
    }

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Uploading a new photo
    if( $form->Filedata->getValue() !== null )
    {
      $db = $user->getTable()->getAdapter();
      $db->beginTransaction();
      
      try
      {
        $fileElement = $form->Filedata;

        $user->setPhoto($fileElement);
        
        $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);
        
        // Insert activity
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'profile_photo_update',
          '{item:$subject} added a new profile photo.');

        // Hooks to enable albums to work
        if( $action ) {
          $event = Engine_Hooks_Dispatcher::_()
            ->callEvent('onUserProfilePhotoUpload', array(
                'user' => $user,
                'file' => $iMain,
              ));

          $attachment = $event->getResponse();
          if( !$attachment ) $attachment = $iMain;

          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
        }
        
        $db->commit();
      }

      // If an exception occurred within the image adapter, it's probably an invalid image
      catch( Engine_Image_Adapter_Exception $e )
      {
        $db->rollBack();
        $form->addError(Zend_Registry::get('Zend_Translate')->_('The uploaded file is not supported or is corrupt.'));
      }

      // Otherwise it's probably a problem with the database or the storage system (just throw it)
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }

    // Resizing a photo
    else if( $form->getValue('coordinates') !== '' )
    {
      $storage = Engine_Api::_()->storage();

      $iProfile = $storage->get($user->photo_id, 'thumb.profile');
      $iSquare = $storage->get($user->photo_id, 'thumb.icon');

      // Read into tmp file
      $pName = $iProfile->getStorageService()->temporary($iProfile);
      $iName = dirname($pName) . '/nis_' . basename($pName);

      list($x, $y, $w, $h) = explode(':', $form->getValue('coordinates'));

      $image = Engine_Image::factory();
      $image->open($pName)
        ->resample($x+.1, $y+.1, $w-.1, $h-.1, 48, 48)
        ->write($iName)
        ->destroy();

      $iSquare->store($iName);

      // Remove temp files
      @unlink($iName);
    }
  }

  public function removePhotoAction()
  {
    // Get form
    $this->view->form = $form = new User_Form_Edit_RemovePhoto();

    if( !$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }
    

    $user = Engine_Api::_()->core()->getSubject();
    $user->photo_id = 0;
    $user->save();
    
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.');

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.'))
    ));
  }

  public function styleAction()
  {
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = $this->_helper->api()->user()->getViewer();
    if( !$this->_helper->requireAuth()->setAuthParams('user', null, 'style')->isValid()) return;


    // Get form
    $this->view->form = $form = new User_Form_Edit_Style();

    // Get current row
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
      ->where('type = ?', $user->getType())
      ->where('id = ?', $user->getIdentity())
      ->limit();

    $row = $table->fetchRow($select);

    // Not posting, populate
    if( !$this->getRequest()->isPost() )
    {
      $form->populate(array(
        'style' => ( null === $row ? '' : $row->style )
      ));
      return;
    }

    // Whoops, form was not valid
    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }


    // Cool! Process
    $style = $form->getValue('style');

    // Process
    $style = strip_tags($style);

    $forbiddenStuff = array(
      '-moz-binding',
      'expression',
      'javascript:',
      'behaviour:',
      'vbscript:',
      'mocha:',
      'livescript:',
    );

    $style = str_replace($forbiddenStuff, '', $style);

    // Save
    if( null == $row )
    {
      $row = $table->createRow();
      $row->type = $user->getType();
      $row->id = $user->getIdentity();
    }

    $row->style = $style;
    $row->save();

    $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
  }

  public function externalPhotoAction()
  {
    if( !$this->_helper->requireSubject()->isValid() ) return;
    $user = Engine_Api::_()->core()->getSubject();
    
    // Get photo
    $photo = Engine_Api::_()->getItemByGuid($this->_getParam('photo'));
    if( !$photo || !($photo instanceof Core_Model_Item_Collectible) || empty($photo->photo_id) )
    {
      $this->_forward('requiresubject', 'error', 'core');
      return;
    }

    if( !$photo->getCollection()->authorization()->isAllowed(null, 'view') )
    {
      $this->_forward('requireauth', 'error', 'core');
      return;
    }

    
    // Make form
    $this->view->form = $form = new User_Form_Edit_ExternalPhoto();
    $this->view->photo = $photo;

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();
    
    try {
      // Get the owner of the photo
      $photoOwnerId = null;
      if( isset($photo->user_id) ) {
        $photoOwnerId = $photo->user_id;
      } else if( isset($photo->owner_id) && (!isset($photo->owner_type) || $photo->owner_type == 'user') ) {
        $photoOwnerId = $photo->owner_id;
      }

      // if it is from your own profile album do not make copies of the image
      if( $photo instanceof Album_Model_Photo &&
          ($photoParent = $photo->getParent()) instanceof Album_Model_Album &&
          $photoParent->owner_id == $photoOwnerId &&
          $photoParent->type == 'profile' ) {

        // ensure thumb.icon and thumb.profile exist
        $newStorageFile = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
        if( $photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.profile') ) {
          try {
            $tmpFile = $newStorageFile->temporary();
            $image = Engine_Image::factory();
            $image->open($tmpFile)
              ->resize(200, 400)
              ->write($tmpFile)
              ->destroy();
            $iProfile = $filesTable->createFile($tmpFile, array(
              'parent_type' => $user->getType(),
              'parent_id' => $user->getIdentity(),
              'user_id' => $user->getIdentity(),
              'name' => basename($tmpFile),
            ));
            $newStorageFile->bridge($iProfile, 'thumb.profile');
            @unlink($tmpFile);
          } catch( Exception $e ) { echo $e; die(); }
        }
        if( $photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.icon') ) {
          try {
            $tmpFile = $newStorageFile->temporary();
            $image = Engine_Image::factory();
            $image->open($tmpFile);
            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;
            $image->resample($x, $y, $size, $size, 48, 48)
              ->write($tmpFile)
              ->destroy();
            $iSquare = $filesTable->createFile($tmpFile, array(
              'parent_type' => $user->getType(),
              'parent_id' => $user->getIdentity(),
              'user_id' => $user->getIdentity(),
              'name' => basename($tmpFile),
            ));
            $newStorageFile->bridge($iSquare, 'thumb.icon');
            @unlink($tmpFile);
          } catch( Exception $e ) { echo $e; die(); }
        }

        // Set it
        $user->photo_id = $photo->file_id;
        $user->save();
        
        // Insert activity
        // @todo maybe it should read "changed their profile photo" ?
        $action = Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $user, 'profile_photo_update',
                '{item:$subject} changed their profile photo.');
        if( $action ) {
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')
              ->attachActivity($action, $photo);
        }
      }

      // Otherwise copy to the profile album
      else {
        $user->setPhoto($photo);

        // Insert activity
        $action = Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $user, 'profile_photo_update',
                '{item:$subject} added a new profile photo.');
        
        // Hooks to enable albums to work
        $newStorageFile = Engine_Api::_()->getItem('storage_file', $user->photo_id);
        $event = Engine_Hooks_Dispatcher::_()
          ->callEvent('onUserProfilePhotoUpload', array(
              'user' => $user,
              'file' => $newStorageFile,
            ));

        $attachment = $event->getResponse();
        if( !$attachment ) {
          $attachment = $newStorageFile;
        }
        
        if( $action  ) {
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')
              ->attachActivity($action, $attachment);
        }
      }

      $db->commit();
    }

    // Otherwise it's probably a problem with the database or the storage system (just throw it)
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Set as profile photo')),
      'smoothboxClose' => true,
    ));
  }

  public function clearStatusAction()
  {
    $this->view->status = false;
    
    if( $this->getRequest()->isPost() ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer->status = '';
      $viewer->status_date = '00-00-0000';
      // twitter-style handling
      // $lastStatus = $viewer->status()->getLastStatus();
      // if( $lastStatus ) {
      //   $viewer->status = $lastStatus->body;
      //   $viewer->status_date = $lastStatus->creation_date;
      // }
      $viewer->save();
      
      $this->view->status = true;
    } 
  }
}