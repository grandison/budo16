<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PhotoController.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_PhotoController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( !Engine_Api::_()->core()->hasSubject() )
    {
      if( 0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
          null !== ($photo = Engine_Api::_()->getItem('classified_photo', $photo_id)) )
      {
        Engine_Api::_()->core()->setSubject($photo);
      }

      else if( 0 !== ($classified_id = (int) $this->_getParam('classified_id')) &&
          null !== ($classified = Engine_Api::_()->getItem('classified', $classified_id)) )
      {
        Engine_Api::_()->core()->setSubject($classified);
      }
    }

    $this->_helper->requireUser->addActionRequires(array(
      'upload',
      'upload-photo', // Not sure if this is the right
      'edit',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
      'list' => 'classified',
      'upload' => 'classified',
      'view' => 'classified_photo',
      'edit' => 'classified_photo',
    ));
  }

  public function listAction()
  {
    $this->view->classified = $classified = Engine_Api::_()->core()->getSubject();
    $this->view->album = $album = $group->getSingletonAlbum();

    $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    $this->view->canUpload = $group->authorization()->isAllowed(null, 'photo.upload');
  }

  public function viewAction()
  {
    $this->view->photo = $photo = Engine_Api::_()->core()->getSubject();
    $this->view->album = $album = $photo->getCollection();
    $this->view->group = $group = $photo->getGroup();
    $this->view->canEdit = $photo->authorization()->isAllowed(null, 'photo.edit');
  }

  public function uploadAction()
  {
    $classified = Engine_Api::_()->core()->getSubject();
    if( isset($_GET['ul']) || isset($_FILES['Filedata']) ) return $this->_forward('upload-photo', null, null, array('format' => 'json', 'classified_id'=>(int) $classified->getIdentity()));

    //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'photo.upload')->isValid() ) return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', (int) $classified->getIdentity());

    $album = $classified->getSingletonAlbum();

    $this->view->classified_id = $classified->classified_id;
    $this->view->form = $form = new Classified_Form_Photo_Upload();
    $form->file->setAttrib('data', array('classified_id' => $classified->getIdentity()));

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('classified_photo');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();
      $params = array(
        'classified_id' => $classified->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      );

      // Add action and attachments
      $api = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $classified, 'classified_photo_upload', null, array('count' => count($values['file'])));

      // Do other stuff
      $count = 0;
      foreach( $values['file'] as $photo_id )
      {
        $photo = Engine_Api::_()->getItem("classified_photo", $photo_id);
        if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) continue;

        /*
        if( $set_cover )
        {
          $album->photo_id = $photo_id;
          $album->save();
          $set_cover = false;
        }
        */

        $photo->collection_id = $album->album_id;
        $photo->album_id = $album->album_id;
        $photo->save();

        if ($classified->photo_id == 0) {
          $classified->photo_id = $photo->file_id;
          $classified->save();
        }

        if( $action instanceof Activity_Model_Action && $count < 8 )
        {
          $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
        }
        $count++;
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }


    $this->_redirectCustom($classified);
  }

  public function uploadPhotoAction()
  {
    $classified = Engine_Api::_()->getItem('classified', (int) $this->_getParam('classified_id'));
    //die('here'.print_r($_GET['ul']).print_r($_FILES['Filedata']).$this->_getParam('classified_id'));

    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    // @todo check auth
    //$classified

    $values = $this->getRequest()->getPost();
    if( empty($values['Filename']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
      return;
    }

    if( !isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    $db = Engine_Api::_()->getDbtable('photos', 'classified')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $album = $classified->getSingletonAlbum();

      $params = array(
        // We can set them now since only one album is allowed
        'collection_id' => $album->getIdentity(),
        'album_id' => $album->getIdentity(),

        'classified_id' => $classified->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      );
      $photo_id = Engine_Api::_()->classified()->createPhoto($params, $_FILES['Filedata'])->photo_id;

      if(!$classified->photo_id){
        $classified->photo_id = $photo_id;
        $classified->save();
      }

      $this->view->status = true;
      $this->view->name = $_FILES['Filedata']['name'];
      $this->view->photo_id = $photo_id;

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      // throw $e;
      return;
    }
  }

  public function editAction()
  {
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'photo.edit')->isValid() ) return;

    $photo = Engine_Api::_()->core()->getSubject();

    $this->view->form = $form = new Classified_Form_Photo_Edit();

    if( !$this->getRequest()->isPost() )
    {
      $form->populate($photo->toArray());
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('photos', 'group')->getAdapter();
    $db->beginTransaction();

    try
    {
      $photo->setFromArray($form->getValues())->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array('Changes saved'),
      'layout' => 'default-simple',
      'parentRefresh' => true,
      'closeSmoothbox' => true,
    ));
  }

  public function removeAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $photo_id= (int) $this->_getParam('photo_id');
    $photo = Engine_Api::_()->getItem('classified_photo', $photo_id);

    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $photo->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }


}