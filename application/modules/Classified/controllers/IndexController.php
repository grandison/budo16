<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8387 2011-02-03 20:53:58Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_IndexController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) return;
  }

  // NONE USER SPECIFIC METHODS
  public function indexAction()
  {
    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) return;
    
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_quick');


    // Prepare form
    $this->view->form = $form = new Classified_Form_Search();
    
    $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('classified', null, 'create')->checkRequire();
    
    $viewer = $this->_helper->api()->user()->getViewer();
    if( !$viewer->getIdentity() )
    {
      $form->removeElement('show');
    }

    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->classified()->getCategories();
    foreach( $categories as $category )
    {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }

    // Process form
    if( $form->isValid($this->getRequest()->getPost()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }

    
    $customFieldValues = array_intersect_key($values, $form->getFieldElements());
    
    // Do the show thingy
    if( @$values['show'] == 2 )
    {
      // Get an array of friend ids to pass to getClassifiedsPaginator
      $table = $this->_helper->api()->getItemTable('user');
      $select = $viewer->membership()->getMembersSelect('user_id');
      $friends = $table->fetchAll($select);
      // Get stuff
      $ids = array();
      foreach( $friends as $friend )
      {
        $ids[] = $friend->user_id;
      }
      //unset($values['show']);
      $values['users'] = $ids;
    }

    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user');
    if ($user_id) $values['user_id'] = $user_id;

    $this->view->assign($values);
    
    // items needed to show what is being filtered in browse page
    if (!empty($values['tag'])) $this->view->tag_text = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
    
    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    $paginator = Engine_Api::_()->classified()->getClassifiedsPaginator($values, $customFieldValues);
    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.page', 10);
    $paginator->setItemCountPerPage($items_count);
    $this->view->paginator = $paginator->setCurrentPageNumber( $this->_getParam('page',1) );

    if( !empty($values['category']) ) {
      $this->view->categoryObject = Engine_Api::_()->classified()->getCategory($values['category']);
    }
  }

  public function viewAction()
  {
    $viewer = $this->_helper->api()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if( $classified )
    {
      Engine_Api::_()->core()->setSubject($classified);
    }

    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams($classified, null, 'view')->isValid() ) {
      return;
    }

    $this->view->canEdit = $canEdit = $classified->authorization()->isAllowed(null, 'edit');
    $this->view->canDelete = $canDelete = $classified->authorization()->isAllowed(null, 'delete');
    $this->view->canUpload = $canUpload = $classified->authorization()->isAllowed(null, 'photo');

    // Get navigation
    $this->view->gutterNavigation = $gutterNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_gutter');
    
    if( $classified ) {
      $this->view->owner = $owner = Engine_Api::_()->getItem('user', $classified->owner_id);
      $this->view->viewer = $viewer;

      if( !$owner->isSelf($viewer) ) {
        $classified->view_count++;
        $classified->save();
      }

      $this->view->classified = $classified;
      if( $classified->photo_id ) {
        $this->view->main_photo = $classified->getPhoto($classified->photo_id);
      }
      
      // get tags
      $this->view->classifiedTags = $classified->tags()->getTagMaps();
      $this->view->userTags = $classified->tags()->getTagsByTagger($classified->getOwner());

      // get custom field values
      //$this->view->fieldsByAlias = Engine_Api::_()->fields()->getFieldsValuesByAlias($classified);
      // Load fields view helpers
      $view = $this->view;
      $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
      $this->view->fieldStructure = $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($classified);

      // album material
      $this->view->album = $album = $classified->getSingletonAlbum();
      $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      $paginator->setItemCountPerPage(100);

      if( $classified->category_id ) {
        $this->view->category = Engine_Api::_()->classified()->getCategory($classified->category_id);
      }
      $this->view->userCategories = Engine_Api::_()->classified()->getUserCategories($this->view->classified->owner_id);
    }
  }

  // USER SPECIFIC METHODS
  public function manageAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_quick');


    $viewer = $this->_helper->api()->user()->getViewer();

    $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('classified', null, 'create')->checkRequire();
    $this->view->allowed_upload = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'photo');
    
    $this->view->form = $form = new Classified_Form_Search();
    $form->removeElement('show');

    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->classified()->getCategories();
    foreach( $categories as $category )
    {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }

    // Process form
    //$request = $this->getRequest()->getPost();

    // Process form
    if( $form->isValid($this->getRequest()->getPost()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    
    //$customFieldValues = $form->getSubForm('custom')->getValues();
    $values['user_id'] = $viewer->getIdentity();

    // custom field search
    $customFieldValues = array_intersect_key($values, $form->getFieldElements());

    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->classified()->getClassifiedsPaginator($values, $customFieldValues);
    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.page', 10);
    $paginator->setItemCountPerPage($items_count);
    $this->view->paginator = $paginator->setCurrentPageNumber( $this->_getParam('page',1) );

    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    // maximum allowed classifieds
    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();
  }

  public function createAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'create')->isValid()) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_main');

    
    $this->view->form = $form = new Classified_Form_Create();
    
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = $this->_helper->api()->getApi('core', 'classified')->getClassifiedsPaginator($values);
    
    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();

    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $table = Engine_Api::_()->getItemTable('classified');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      // Create classified
      $values = array_merge($form->getValues(), array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
      ));

      $classified = $table->createRow();
      $classified->setFromArray($values);
      $classified->save();

      // Set photo
      if( !empty($values['photo']) ) {
        $classified->setPhoto($form->photo);
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $tags = array_filter(array_map("trim", $tags));
      $classified->tags()->addTagMaps($viewer, $tags);

      // Add fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($classified);
      $customfieldform->saveValues();

      // Set privacy
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = array("everyone");
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = array("everyone");
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($classified, $role, 'view',    ($i <= $viewMax));
        $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $db->beginTransaction();
    try {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $classified, 'classified_new');
      if( $action != null ) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $classified);
      }
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    // Redirect
    $allowed_upload = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'photo');
    if( $allowed_upload ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'success', 'classified_id' => $classified->classified_id), 'classified_specific', true);
    } else {
      return $this->_helper->redirector->gotoUrl($classified->getHref(), array('prependBase' => false));
    }
  }

  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    $viewer = $this->_helper->api()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if( !Engine_Api::_()->core()->hasSubject('classified') ) {
      Engine_Api::_()->core()->setSubject($classified);
    }
    $this->view->classified = $classified;
    
    // Check auth
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'edit')->isValid() ) {
      return;
    }
    
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_main', array(), 'classified_main_manage');


    // Prepare form
    $this->view->form = $form = new Classified_Form_Edit(array(
      'item' => $classified
    ));
    
    $form->removeElement('photo');

    //die("<pre>".print_r($aliasValues->toArray(),true)."</pre>");
    //$customfieldform->getFieldsValuesByAlias($classified);
    
    $this->view->album = $album = $classified->getSingletonAlbum();
    $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage(100);
    
    foreach( $paginator as $photo )
    {
      $subform = new Classified_Form_Photo_Edit(array('elementsBelongTo' => $photo->getGuid()));
      $subform->removeElement('title');

      $subform->populate($photo->toArray());
      $form->addSubForm($subform, $photo->getGuid());
      $form->cover->addMultiOption($photo->getIdentity(), $photo->getIdentity());
    }
    // Save classified entry
    $saved = $this->_getParam('saved');
    if( !$this->getRequest()->isPost() || $saved )
    {

      if( $saved )
      {
        $url = $this->_helper->url->url(array('user_id' => $viewer->getIdentity(), 'classified_id' => $classified->getIdentity()), 'classified_entry_view');
        $savedChangesNotice = Zend_Registry::get('Zend_Translate')->_("Your changes were saved. Click %s to view your listing.",'<a href="'.$url.'">here</a>');
        $form->addNotice($savedChangesNotice);
      }

      // prepare tags
      $classifiedTags = $classified->tags()->getTagMaps();
      //$form->getSubForm('custom')->saveValues();
      
      $tagString = '';
      foreach( $classifiedTags as $tagmap )
      {
        if( $tagString !== '' ) $tagString .= ', ';
        $tagString .= $tagmap->getTag()->getTitle();
      }

      $this->view->tagNamePrepared = $tagString;
      $form->tags->setValue($tagString);

      // etc
      $form->populate($classified->toArray());
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach( $roles as $role )
      {
        if( 1 === $auth->isAllowed($classified, $role, 'view') )
        {
          $form->auth_view->setValue($role);
        }
        if( 1 === $auth->isAllowed($classified, $role, 'comment') )
        {
          $form->auth_comment->setValue($role);
        }
      }

      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process

    // handle save for tags
    $values = $form->getValues();
    $tags = preg_split('/[,]+/', $values['tags']);
    $tags = array_filter(array_map("trim", $tags));

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
      $classified->setFromArray($values);
      $classified->modified_date = date('Y-m-d H:i:s');

      $classified->tags()->setTagMaps($viewer, $tags);
      $classified->save();

      $cover = $values['cover'];

      // Process
      foreach( $paginator as $photo )
      {
        $subform = $form->getSubForm($photo->getGuid());
        $subValues = $subform->getValues();
        $subValues = $subValues[$photo->getGuid()];
        unset($subValues['photo_id']);

        if( isset($cover) && $cover == $photo->photo_id) {
          $classified->photo_id = $photo->file_id;
          $classified->save();
        }

        if( isset($subValues['delete']) && $subValues['delete'] == '1' )
        {
          if( $classified->photo_id == $photo->file_id ){
            $classified->photo_id = 0;
            $classified->save();
          }
          $photo->delete();
        }
        else
        {
          $photo->setFromArray($subValues);
          $photo->save();
        }
      }

      // Save custom fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($classified);
      $customfieldform->saveValues();

      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if( !empty($values['auth_view']) ) {
        $auth_view = $values['auth_view'];
      } else {
        $auth_view = "everyone";
      }
      $viewMax = array_search($auth_view, $roles);

      foreach( $roles as $i=>$role )
      {
        $auth->setAllowed($classified, $role, 'view', ($i <= $viewMax));
      }

      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if( !empty($values['auth_comment']) ) {
        $auth_comment =$values['auth_comment'];
      } else {
        $auth_comment = "everyone";
      }
      $commentMax = array_search($auth_comment, $roles);
      
      foreach ($roles as $i=>$role)
      {
        $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }

      $db->commit();

    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }


    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($classified) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'classified_general', true);
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->getRequest()->getParam('classified_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($classified, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $this->view->form = $form = new Classified_Form_Delete();

    if( !$classified )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Classified listing doesn't exist or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $classified->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $classified->delete();
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your classified listing has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'classified_general', true),
      'messages' => Array($this->view->message)
    ));
  }
  
  public function closeAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    $viewer = $this->_helper->api()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if( !Engine_Api::_()->core()->hasSubject('classified') ) {
      Engine_Api::_()->core()->setSubject($classified);
    }
    $this->view->classified = $classified;

    // Check auth
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'edit')->isValid() ) {
      return;
    }

    // @todo convert this to post only

    $table = $classified->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $classified->closed = $this->_getParam('closed');
      $classified->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    if( !($returnUrl = $this->_getParam('return_url')) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'classified_general', true);
    } else {
      return $this->_redirect($returnUrl, array('prependBase' => false));
    }
  }
  
  public function successAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('classified_main', array(), 'classified_main_manage');
    

    $viewer = $this->_helper->api()->user()->getViewer();
    $this->view->classified = $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));

    if( $viewer->getIdentity() != $classified->owner_id )
    {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if( $this->getRequest()->isPost() && $this->getRequest()->getPost('confirm') == true )
    {
      return $this->_redirect("classifieds/photo/upload/subject/classified_".$this->_getParam('classified_id'));
    }
  }
}

