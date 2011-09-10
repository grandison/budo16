<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8405 2011-02-05 03:36:15Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_IndexController extends Core_Controller_Action_Standard
{
  public function init()
  {
    //$this->getNavigation();
    
    // only show videos if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) return;

    $id = $this->_getParam('video_id', $this->_getParam('id', null));
    if( $id )
    {
      $video = Engine_Api::_()->getItem('video', $id);
      if( $video )
      {
        Engine_Api::_()->core()->setSubject($video);
      }
    }
    if( !$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) return;
  }

  public function browseAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('video_main', array(), 'video_main_browse');

    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Video_Form_Search();
    $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();
    
    // Process form
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    $this->view->formValues = $values;

    $values['status'] = 1;
    $values['search'] = 1;

    $this->view->category = $values['category'];


    if( !empty($values['tag']) ) {
      $this->view->tag = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
    }
    
    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user');
    if( $user_id ) {
      $values['user_id'] = $user_id;
    }

    $this->view->paginator = $paginator = Engine_Api::_()->getApi('core', 'video')->getVideosPaginator($values);

    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 10);
    $paginator->setItemCountPerPage($items_count);
    
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
  public function rateAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    
    $rating = $this->_getParam('rating');
    $video_id =  $this->_getParam('video_id');

    
    $table = Engine_Api::_()->getDbtable('ratings', 'video');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      Engine_Api::_()->video()->setRating($video_id, $user_id, $rating);
         
      $video = Engine_Api::_()->getItem('video', $video_id);
      $video->rating = Engine_Api::_()->video()->getRating($video->getIdentity());
      $video->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    $data = array();
    $data[] = array(
      'total' => $total,
      'rating' => $rating,
    );
    return $this->_helper->json($data);
    $data = Zend_Json::encode($data);
    $this->getResponse()->setBody($data);
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams('video', null, 'create')->isValid()) return;

    // Upload video
    if( isset($_GET['ul']) || isset($_FILES['Filedata']) ) {
      return $this->_forward('upload-video', null, null, array('format' => 'json'));
    }
    
    // Get navigation
    $this->view->navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('video_main', array(), 'video_main_create');
    
    // set up data needed to check quota
    $viewer = $this->_helper->api()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = $this->_helper->api()->getApi('core', 'video')->getVideosPaginator($values);

    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();

    // Create form
    $this->view->form = $form = new Video_Form_Video();

    if ($this->_getParam('type', false))
      $form->getElement('type')->setValue( $this->_getParam('type') );
    
    // if this is from a failed attempt
    if ($this->_getParam('retry'))
    {/*
      $video = Engine_Api::_()->getItem('video', $this->_getParam('retry'));
      $form->getElement('search')->setValue($video->search);
      $form->getElement('title')->setValue($video->title);
      $form->getElement('description')->setValue($video->description);
      $form->getElement('category_id')->setValue($video->category_id);
      // prepare tags
      $videoTags = $video->tags()->getTagMaps();

      $tagString = '';
      foreach( $videoTags as $tagmap )
      {
        if( $tagString !== '' ) $tagString .= ', ';
        $tagString .= $tagmap->getTag()->getTitle();
      }

      $this->view->tagNamePrepared = $tagString;
      $form->tags->setValue($tagString);
      // get more information?
      // delete the video?
      $video->delete();*/
    }

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $values = $form->getValues('url');
      //$form->set = $values['url'];
      // set title and description using getinfromation() here?
      return;
    }

    // Process
    $values = $form->getValues();
    $values['owner_id'] = $viewer->getIdentity();

    $insert_action = false;

    $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
    $db->beginTransaction();

    try
    {
      // Create video
      $table = $this->_helper->api()->getDbtable('videos', 'video');
      if($values['type']==3){
        $video = Engine_Api::_()->getItem('video', $this->_getParam('id'));
      }
      else $video = $table->createRow();
      
      $video->setFromArray($values);
      $video->save();
      
      // Now try to create thumbnail
      $thumbnail = $this->handleThumbnail($video->type, $video->code);
      $ext = ltrim(strrchr($thumbnail, '.'), '.');
      $thumbnail_parsed = @parse_url($thumbnail);

      if (@GetImageSize($thumbnail)) {
      $valid_thumb = true;
      } else {
      $valid_thumb = false;
      }

      if( $valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) )
      {
        $tmp_file = APPLICATION_PATH . '/temporary/link_'.md5($thumbnail).'.'.$ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_'.md5($thumbnail).'.'.$ext;

        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);

        $image = Engine_Image::factory();
        $image->open($tmp_file)
          ->resize(120, 240)
          ->write($thumb_file)
          ->destroy();

        try {
          $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
            'parent_type' => $video->getType(),
            'parent_id' => $video->getIdentity()
          ));

          // Remove temp file
          @unlink($thumb_file);
          @unlink($tmp_file);

        }
        catch (Exception $e)
        {

        }
        $information = $this->handleInformation($video->type, $video->code);

        $video->duration = $information['duration'];
        if (!$video->description) $video->description = $information['description'];
        $video->photo_id = $thumbFileRow->file_id;
        $video->status = 1;
        $video->save();

         // Insert new action item
        $insert_action = true;        
      }

      if ($values['ignore']==true){
        $video->status = 1;
        $video->save();
        $insert_action = true;
      }
      
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if(isset($values['auth_view'])) $auth_view =$values['auth_view'];
      else $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach( $roles as $i=>$role )
      {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }

      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if(isset($values['auth_comment'])) $auth_comment =$values['auth_comment'];
      else $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i=>$role)
      {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }
      

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->addTagMaps($viewer, $tags);
      

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }


    $db->beginTransaction();
    try {
      if($insert_action){
        $owner = $video->getOwner();
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'video_new');
        if($action!=null){
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
        }
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($video) as $action ) {
        $actionTable->resetActivityBindings($action);
      }


      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    if ($video->type == 3){
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'video_general', true);
    }
    return $this->_helper->redirector->gotoRoute(array('user_id' => $viewer->getIdentity(), 'video_id' => $video->getIdentity()), 'video_view', true);
  }

  public function uploadVideoAction()
  {
    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $values = $this->getRequest()->getPost();

    if( empty($values['Filename']) )
    {
      $this->view->status = false;
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_('No file');
      return;
    }

    if( !isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid Upload').print_r($_FILES, true);
      return;
    }

    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt');
    if( in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions) )
    {
      $this->view->status = false;
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();

      $params = array(
        'owner_type' => 'user',
        'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->video()->createVideo($params, $_FILES['Filedata'], $values);

      $this->view->status   = true;
      $this->view->name     = $_FILES['Filedata']['name'];
      $this->view->code = $video->code;
      $this->view->video_id = $video->video_id;

      // sets up title and owner_id now just incase members switch page as soon as upload is completed
      $video->title = $_FILES['Filedata']['name'];
      $video->owner_id = $viewer->getIdentity();
      $video->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.').$e;
      // throw $e;
      return;
    }
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('video', $this->getRequest()->getParam('video_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $this->view->form = $form = new Video_Form_Delete();

    if( !$video )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Video doesn't exists or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      Engine_Api::_()->getApi('core', 'video')->deleteVideo($video);
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'video_general', true),
      'messages' => Array($this->view->message)
    ));
  }
  
  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    $viewer = Engine_Api::_()->user()->getViewer();

    $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));
    //Engine_Api::_()->core()->setSubject($video);
    if( !$this->_helper->requireSubject()->isValid() ) return;


    if( $viewer->getIdentity() != $video->owner_id && !$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid())
    {
      return $this->_forward('requireauth', 'error', 'core');
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('video_main', array(), 'video_main_manage');
    
    $this->view->video = $video;
    $this->view->form = $form = new Video_Form_Edit();
    $form->getElement('search')->setValue($video->search);
    $form->getElement('title')->setValue($video->title);
    $form->getElement('description')->setValue($video->description);
    $form->getElement('category_id')->setValue($video->category_id);


    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    foreach( $roles as $role )
    {
      if( 1 === $auth->isAllowed($video, $role, 'view') )
      {
        $form->auth_view->setValue($role);
      }
      if( 1 === $auth->isAllowed($video, $role, 'comment') )
      {
        $form->auth_comment->setValue($role);
      }
    }
    
    // prepare tags
    $videoTags = $video->tags()->getTagMaps();

    $tagString = '';
    foreach( $videoTags as $tagmap )
    {
      if( $tagString !== '' ) $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }

    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      $video->setFromArray($values);
      $video->save();

      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if($values['auth_view']) $auth_view =$values['auth_view'];
      else $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach( $roles as $i=>$role )
      {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }

      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if($values['auth_comment']) $auth_comment =$values['auth_comment'];
      else $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i=>$role)
      {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->setTagMaps($viewer, $tags);

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($video) as $action ) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }


    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'video_general', true);
  }

  public function uploadAction()
  {
    if( isset($_GET['ul']) || isset($_FILES['Filedata']) ) return $this->_forward('upload-video', null, null, array('format' => 'json'));

    if( !$this->_helper->requireUser()->isValid() ) return;

    $this->view->form = $form = new Video_Form_Video();
    $this->view->navigation = $this->getNavigation();

    if( !$this->getRequest()->isPost() )
    {
      if( null !== ($album_id = $this->_getParam('album_id')) )
      {
        $form->populate(array(
          'album' => $album_id
        ));
      }
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    $album = $form->saveValues();
    //$this->_helper->redirector->gotoRoute(array('album_id'=>$album->album_id), 'album_editphotos', true);
  }

  public function viewAction()
  {
    //$video_id = $this->_getParam('video_id');
    //$video = Engine_Api::_()->getItem('video', $video_id);
    //if( $video ) Engine_Api::_()->core()->setSubject($video);
    if( !$this->_helper->requireSubject()->isValid() ) return;

    $video = Engine_Api::_()->core()->getSubject('video');
    $viewer = $this->_helper->api()->user()->getViewer();

    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_id = $this->getRequest()->getParam('message');
    $message_view = false;
    if ($message_id){
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) $message_view = true;
    }
    $this->view->message_view = $message_view;
    if( !$message_view && !$this->_helper->requireAuth()->setAuthParams($video, null, 'view')->isValid()) return;

    $this->view->videoTags = $video->tags()->getTagMaps();

    $can_edit = $this->view->can_edit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->checkRequire();
    $can_delete = $this->view->can_delete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->checkRequire();
    

    // increment count
    $embedded = "";
    if ($video->status==1){
      $video->view_count++;
      $video->save();
      $embedded = $video->getRichContent(true);
    }
    
    if( $video->type == 3 && $video->status != 0 ) {
      $video->view_count++;
      $video->save();

      if( !empty($video->file_id) ) {
        $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
        if( $storage_file ) {
          $this->view->video_location = $storage_file->map();
        }
      }
    }

    $this->view->viewer_id = $viewer->getIdentity();
    $this->view->rating_count = Engine_Api::_()->video()->ratingCount($video->getIdentity());
    $this->view->video = $video;
    $this->view->rated = Engine_Api::_()->video()->checkRated($video->getIdentity(), $viewer->getIdentity());
    //Zend_Registry::get('Zend_View')?
    $this->view->videoEmbedded = $embedded;
    if($video->category_id !=0) $this->view->category = Engine_Api::_()->video()->getCategory($video->category_id);

    // Render
//    $this->_helper->content
//        //->setNoRender()
//        ->setEnabled()
//        ;
  }

  public function manageAction()
  {
    $viewer = $this->_helper->api()->user()->getViewer();
    if( !$this->_helper->requireUser()->isValid() ) return;
    $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();

    // Get navigation
    $this->view->navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('video_main', array(), 'video_main_manage');

    // prepare categories
    $this->view->form = $form = new Video_Form_Search();
    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->video()->getCategories();
    foreach( $categories as $category )
    {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }
    // Process form
    $form->isValid($this->_getAllParams());
    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $this->view->category = $values['category'];

    $this->view->paginator = $paginator =
      $this->_helper->api()->getApi('core', 'video')->getVideosPaginator($values);

    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 10);
    $this->view->paginator->setItemCountPerPage($items_count);
    
    $this->view->paginator->setCurrentPageNumber( $this->_getParam('page',1) );

    // maximum allowed videos
    $this->view->quota = $quota = (int) Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();
  }
  
  public function composeUploadAction()
  {
    $viewer = $this->_helper->api()->user()->getViewer();

    if( !$viewer->getIdentity() )
    {
      $this->_redirect('login');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
      return;
    }

    $video_title = $this->_getParam('title');
    $video_url = $this->_getParam('uri');
    $video_type = $this->_getParam('type');

    // extract code
    //$code = $this->extractCode("http://www.youtube.com/watch?v=5osJ8-NttnU&feature=popt00us08", $video_type);
    //$code = parse_url("http://vimeo.com/3945157/asd243", PHP_URL_PATH);

    $code = $this->extractCode($video_url, $video_type);
    // check if code is valid
        // check which API should be used
    if ($video_type==1){
      $valid = $this->checkYouTube($code);
    }
    if ($video_type==2){
      $valid = $this->checkVimeo($code);
    }


    // check to make sure the user has not met their quota of # of allowed video uploads
    // set up data needed to check quota
    $values['user_id'] = $viewer->getIdentity();
    $paginator = $this->_helper->api()->getApi('core', 'video')->getVideosPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
    $current_count = $paginator->getTotalItemCount();
    
    if (($current_count >= $quota)&& !empty($quota)){
      // return error message
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You have already uploaded the maximum number of videos allowed. If you would like to upload a new video, please delete an old one first.');
    }


    else if ($valid){
      $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
      $db->beginTransaction();

      try
      {
        $information = $this->handleInformation($video_type, $code);
      // create video
        $table = $this->_helper->api()->getDbtable('videos', 'video');
        $video = $table->createRow();
        $video->title = $information['title'];
        $video->description = $information['description'];
        $video->duration = $information['duration'];
        $video->owner_id = $viewer->getIdentity();
        $video->code =$code;
        $video->type = $video_type;
        $video->save();

        // Now try to create thumbnail
        $thumbnail = $this->handleThumbnail($video->type, $video->code);
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);

        $tmp_file = APPLICATION_PATH . '/temporary/link_'.md5($thumbnail).'.'.$ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_'.md5($thumbnail).'.'.$ext;

        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);

        $image = Engine_Image::factory();
        $image->open($tmp_file)
          ->resize(120, 240)
          ->write($thumb_file)
          ->destroy();

        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
          'parent_type' => $video->getType(),
          'parent_id' => $video->getIdentity()
        ));

        $video->photo_id = $thumbFileRow->file_id;
        $video->status = 1;
        $video->save();
        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      $type = $this->_getParam('c_type', 'wall');

      // make the video public
      if ($type === 'wall'){
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach( $roles as $i=>$role )
        {
          $auth->setAllowed($video, $role, 'view', ($i <= $roles));
          $auth->setAllowed($video, $role, 'comment', ($i <= $roles));
        }
      }
      
      $this->view->status = true;
      $this->view->video_id = $video->video_id;
      $this->view->photo_id = $video->photo_id;
      $this->view->title = $video->title;
      $this->view->description = $video->description;
      $this->view->src = $video->getPhotoUrl();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video posted successfully');
    }
    else {
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('We could not find a video there - please check the URL and try again.');
    }
  }

  public function validationAction(){
    $video_type = $this->_getParam('type');
    $code = $this->_getParam('code');
    $ajax = $this->_getParam('ajax', false);
    $valid = false;

    // check which API should be used
    if ($video_type=="youtube"){
      $valid = $this->checkYouTube($code);
    }
    if ($video_type=="vimeo"){
      $valid = $this->checkVimeo($code);
    }

    $this->view->code = $code;
    $this->view->ajax = $ajax;
    $this->view->valid = $valid;
  }

  public function getNavigation()
  {
    $this->view->navigation = $navigation = new Zend_Navigation();
    $navigation->addPage(array(
      'label' => 'Browse Videos',
      'route' => 'video_general',
      'action' => 'browse',
      'controller' => 'index',
      'module' => 'video'
    ));

    if( Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
      $navigation->addPages(array(
        array(
          'label' => 'My Videos',
          'route' => 'video_general',
          'action' => 'manage',
          'controller' => 'index',
          'module' => 'video'
        ),
        array(
          'label' => 'Post New Video',
          'route' => 'video_general',
          'action' => 'create',
          'controller' => 'index',
          'module' => 'video'
        )
      ));
    }

    return $navigation;
  }

  // HELPER FUNCTIONS

  public function extractCode($url, $type){
    switch ($type) {
      //youtube
      case "1":
        // change new youtube URL to old one
        $url= preg_replace("/#!/", "?", $url);

        // get v variable from the url
        $arr = array();
        $arr = @parse_url($url);
        $code = "code";
        $parameters = $arr["query"];
        parse_str($parameters, $data);
        $code = $data['v'];
        
        return $code;
      //vimeo
      case "2":
      // get the first variable after slash
        $code = @pathinfo($url);
        return $code['basename'];
    }
  }

  // YouTube Functions
  public function checkYouTube($code){
    if (!$data = @file_get_contents("http://gdata.youtube.com/feeds/api/videos/".$code)) return false;
    if ($data == "Video not found") return false;
    return true;
  }

  // Vimeo Functions
  public function checkVimeo($code){
    //http://www.vimeo.com/api/docs/simple-api
    //http://vimeo.com/api/v2/video
    $data = @simplexml_load_file("http://vimeo.com/api/v2/video/".$code.".xml");
    $id = count($data->video->id);
    if ($id == 0) return false;
    return true;
  }

  // handles thumbnails
  public function handleThumbnail($type, $code = null){
    switch ($type) {
      //youtube
      case "1":
        //http://img.youtube.com/vi/Y75eFjjgAEc/default.jpg
        return "http://img.youtube.com/vi/$code/default.jpg";
      //vimeo
      case "2":
        //thumbnail_medium
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/".$code.".xml");
        $thumbnail = $data->video->thumbnail_medium;
        return $thumbnail;
    }  
  }


  // retrieves infromation and returns title + desc
  public function handleInformation($type, $code){
    switch ($type) {
      //youtube
      case "1":
        $yt = new Zend_Gdata_YouTube();
        $youtube_video = $yt->getVideoEntry($code);        
        $information = array();
        $information['title'] = $youtube_video->getTitle();
        $information['description'] = $youtube_video->getVideoDescription();
        $information['duration'] = $youtube_video->getVideoDuration();
        //http://img.youtube.com/vi/Y75eFjjgAEc/default.jpg
        return $information;
      //vimeo
      case "2":
        //thumbnail_medium
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/".$code.".xml");
        $thumbnail = $data->video->thumbnail_medium;
        $information = array();
        $information['title'] =  $data->video->title;
        $information['description'] = $data->video->description;
        $information['duration'] = $data->video->duration;
        //http://img.youtube.com/vi/Y75eFjjgAEc/default.jpg
        return $information;
    }
  }

}
