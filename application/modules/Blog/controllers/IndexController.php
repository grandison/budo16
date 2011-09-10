<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8298 2011-01-25 02:02:59Z jung $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Blog_IndexController extends Core_Controller_Action_Standard
{
  public function init()
  {
    // only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'view')->isValid() ) return;
  }

  public function indexAction()
  {
    // Enable content helper?
    //$this->_helper->content->setEnabled();
    
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_quick');

    // Prepare data
    $viewer = $this->_helper->api()->user()->getViewer();
    //if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'view')->isValid()) return;
    
    $this->view->form = $form = new Blog_Form_Search();
    $this->view->canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

    $form->removeElement('draft');
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }

    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->blog()->getCategories();
    foreach( $categories as $category )
    {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }

    // Process form
    $form->isValid($this->_getAllParams());
    $values = $form->getValues();
    $this->view->formValues = array_filter($values);
    $values['draft'] = "0";
    $values['visible'] = "1";

    // Do the show thingy
    if( @$values['show'] == 2 )
    {
      // Get an array of friend ids to pass to getBlogsPaginator
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
    
    $this->view->assign($values);

    $paginator = Engine_Api::_()->blog()->getBlogsPaginator($values);

    $items_per_page = Engine_Api::_()->getApi('settings', 'core')->blog_page;
    $paginator->setItemCountPerPage($items_per_page);

    $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );

    if( !empty($values['category']) ) $this->view->categoryObject = Engine_Api::_()->blog()->getCategory($values['category']);
  }
  
  public function viewAction()
  {
    // Check permission
    $viewer = $this->_helper->api()->user()->getViewer();
    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( $blog ) {
      Engine_Api::_()->core()->setSubject($blog);
    }

    if( !$this->_helper->requireSubject()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'view')->isValid()) return;

    // Get navigation
    $this->view->gutterNavigation = $gutterNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_gutter');
    
    // Prepare data
    $archiveList = Engine_Api::_()->blog()->getArchiveList($blog->owner_id);

    $this->view->archive_list = $this->_handleArchiveList($archiveList);
    $this->view->viewer = $viewer;
    $blog->view_count++;
    $blog->save();

    $this->view->blog = $blog;

    $this->view->blogTags = $blog->tags()->getTagMaps();
    $this->view->userTags = $blog->tags()->getTagsByTagger($blog->getOwner());
    //$this->view->blogTags = Engine_Api::_()->blog()->getBlogTags($blog_id);
    //$this->view->userTags = Engine_Api::_()->blog()->getUserTags($blog->owner_id);

    if($blog->category_id !=0) $this->view->category = Engine_Api::_()->blog()->getCategory($blog->category_id);
    $this->view->userCategories = Engine_Api::_()->blog()->getUserCategories($this->view->blog->owner_id);

    // Get styles
    $this->view->owner = $user = $blog->getOwner();
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
      ->where('type = ?', 'user_blog')
      ->where('id = ?', $user->getIdentity())
      ->limit();

    $row = $table->fetchRow($select);

    if( null !== $row && !empty($row->style) )
    {
      $this->view->headStyle()->appendStyle($row->style);
    }
  }

  // USER SPECIFIC METHODS
  public function manageAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_quick');

    // Prepare data
    $viewer = $this->_helper->api()->user()->getViewer();
    $this->view->form = $form = new Blog_Form_Search();
    $this->view->canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

    $form->removeElement('show');
    
    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->blog()->getCategories();
    foreach( $categories as $category ) {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }

    // Process form
    $form->isValid($this->_getAllParams());
    $values = $form->getValues();
    $this->view->formValues = array_filter($values);
    $values['user_id'] = $viewer->getIdentity();

    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->blog()->getBlogsPaginator($values);
    $items_per_page = Engine_Api::_()->getApi('settings', 'core')->blog_page;
    $paginator->setItemCountPerPage($items_per_page);
    $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );
  }

  public function listAction()
  {
    // Preload info
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->owner = $owner = Engine_Api::_()->getItem('user', $this->_getParam('user_id'));

    // Get navigation
    $this->view->gutterNavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_gutter', array('user_id' => $owner->getIdentity()));

    $archiveList = Engine_Api::_()->blog()->getArchiveList($owner->getIdentity());
    $this->view->archive_list = $this->_handleArchiveList($archiveList);

    // Make form
    $this->view->form = $form = new Blog_Form_Search();
    $form->removeElement('show');

    // Populate form
    $this->view->categories = $categories = Engine_Api::_()->blog()->getCategories();
    foreach( $categories as $category ) {
      $form->category->addMultiOption($category->category_id, $category->category_name);
    }

    // Process form
    $form->isValid($this->_getAllParams());
    $values = $form->getValues();
    $this->view->formValues = array_filter($values);
    $values['user_id'] = $owner->getIdentity();
    $values['draft'] = "0";
    $values['visible'] = "1";


    $this->view->assign($values);

    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->blog()->getBlogsPaginator($values);
    $items_per_page = Engine_Api::_()->getApi('settings', 'core')->blog_page;
    $paginator->setItemCountPerPage($items_per_page);
    $this->view->paginator = $paginator->setCurrentPageNumber( $values['page'] );

    $this->view->userTags = Engine_Api::_()->getDbtable('tags', 'core')->getTagsByTagger('blog', $owner);
    $this->view->userCategories = Engine_Api::_()->blog()->getUserCategories($owner->getIdentity());
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->isValid()) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_main');

    // Prepare form
    $this->view->form = $form = new Blog_Form_Create();
            
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $table = Engine_Api::_()->getItemTable('blog');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      // Create blog
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = array_merge($form->getValues(), array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
      ));

      $blog = $table->createRow();
      $blog->setFromArray($values);
      $blog->save();

      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }
      
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->addTagMaps($viewer, $tags);

      // Add activity only if blog is published
      if( $values['draft'] == 0 ) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new');

        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
        }

      }

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
  }

  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    $viewer = $this->_helper->api()->user()->getViewer();
    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( !Engine_Api::_()->core()->hasSubject('blog') ) {
      Engine_Api::_()->core()->setSubject($blog);
    }

    if( !$this->_helper->requireSubject()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'edit')->isValid() ) return;

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('blog_main');

    // Prepare form
    $this->view->form = $form = new Blog_Form_Edit();

    // Populate form
    $form->populate($blog->toArray());

    $tagStr = '';
    foreach( $blog->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      if( '' !== $tagStr ) $tagStr .= ', ';
      $tagStr .= $tag->text;
    }
    $form->populate(array(
      'tags' => $tagStr,
    ));
    $this->view->tagNamePrepared = $tagStr;

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($blog, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($blog, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
    }

    // hide status change if it has been already published
    if( $blog->draft == "0" ) {
      $form->removeElement('draft');
    }


    // Check post/form
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    
    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    
    try
    {
      $values = $form->getValues();

      $blog->setFromArray($values);
      $blog->modified_date = date('Y-m-d H:i:s');
      $blog->save();

      // Auth
      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->setTagMaps($viewer, $tags);

      // insert new activity if blog is just getting published
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($blog);
      if( count($action->toArray()) <= 0 && $values['draft'] == '0' ) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new');
          // make sure action exists before attaching the blog to the activity
        if( $action != null ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
        }
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($blog) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();

    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $blog = Engine_Api::_()->getItem('blog', $this->getRequest()->getParam('blog_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($blog, null, 'delete')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    $this->view->form = $form = new Blog_Form_Delete();

    if( !$blog )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Blog entry doesn't exist or not authorized to delete");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $blog->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $blog->delete();
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your blog entry has been deleted.');
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'blog_general', true),
      'messages' => Array($this->view->message)
    ));
  }

  public function styleAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'style')->isValid()) return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Require user
    if( !$this->_helper->requireUser()->isValid() ) return;
    $user = Engine_Api::_()->user()->getViewer();

    // Make form
    $this->view->form = $form = new Blog_Form_Style();

    // Get current row
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
      ->where('type = ?', 'user_blog') // @todo this is not a real type
      ->where('id = ?', $user->getIdentity())
      ->limit(1);

    $row = $table->fetchRow($select);

    // Check post
    if( !$this->getRequest()->isPost() )
    {
      $form->populate(array(
        'style' => ( null === $row ? '' : $row->style )
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Cool! Process
    $style = $form->getValue('style');
    
    // Save
    if( null == $row )
    {
      $row = $table->createRow();
      $row->type = 'user_blog'; // @todo this is not a real type
      $row->id = $user->getIdentity();
    }

    $row->style = $style;
    $row->save();

    $this->view->draft = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_("Your changes have been saved.");
    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => false,
        'messages' => array($this->view->message)
    ));
  }




  // Utility

  /**
   * Returns an array of dates where a given user created a blog entry
   *
   * @param Zend_Db_Table_Select collection of dates
   * @return Array Dates
   */
  protected function _handleArchiveList($results)
  {
    $localeObject = Zend_Registry::get('Locale');
    
    $blog_dates = array();
    foreach ($results as $result)
      $blog_dates[] = strtotime($result->creation_date);

    // GEN ARCHIVE LIST
    $time = time();
    $archive_list = array();

    foreach( $blog_dates as $blog_date )
    {
      $ltime = localtime($blog_date, TRUE);
      $ltime["tm_mon"] = $ltime["tm_mon"] + 1;
      $ltime["tm_year"] = $ltime["tm_year"] + 1900;

      // LESS THAN A YEAR AGO - MONTHS
      if( $blog_date+31536000>$time )
      {
        $date_start = mktime(0, 0, 0, $ltime["tm_mon"], 1, $ltime["tm_year"]);
        $date_end = mktime(0, 0, 0, $ltime["tm_mon"]+1, 1, $ltime["tm_year"]);
        //$label = date('F Y', $blog_date);
        $type = 'month';

        $dateObject = new Zend_Date($blog_date);
        $format = $localeObject->getTranslation('yMMMM', 'dateitem', $localeObject);
        $label = $dateObject->toString($format, $localeObject);
      }

      // MORE THAN A YEAR AGO - YEARS
      else
      {
        $date_start = mktime(0, 0, 0, 1, 1, $ltime["tm_year"]);
        $date_end = mktime(0, 0, 0, 1, 1, $ltime["tm_year"]+1);
        //$label = date('Y', $blog_date);
        $type = 'year';

        $dateObject = new Zend_Date($blog_date);
        $format = $localeObject->getTranslation('yyyy', 'dateitem', $localeObject);
        if( !$format ) {
          $format = $localeObject->getTranslation('y', 'dateitem', $localeObject);
        }
        $label = $dateObject->toString($format, $localeObject);
      }

      if( !isset($archive_list[$date_start]) )
      {
        $archive_list[$date_start] = array(
          'type' => $type,
          'label' => $label,
          'date_start' => $date_start,
          'date_end' => $date_end,
          'count' => 1
        );
      }
      else
      {
        $archive_list[$date_start]['count']++;
      }
    }

    //krsort($archive_list);
    return $archive_list;
  }
}

