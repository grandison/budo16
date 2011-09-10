<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: TopicController.php 8095 2010-12-21 02:58:28Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_TopicController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( 0 !== ($topic_id = (int) $this->_getParam('topic_id')) &&
        null !== ($topic = Engine_Api::_()->getItem('forum_topic', $topic_id)) &&
        $topic instanceof Forum_Model_Topic ) {
      Engine_Api::_()->core()->setSubject($topic);
    }
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.delete') ) {
      return;
    }
    
    $this->view->form = $form = new Forum_Form_Topic_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_topic');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $topic->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic deleted.')),
      'layout' => 'default-simple',
      'parentRedirect' => $forum->getHref(),
    ));
  }

  public function editAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit') ) {
      return;
    }

    $this->view->form = $form = new Forum_Form_Topic_Edit();

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_topic');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $topic->setFromArray($values);
      $topic->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function viewAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum =  $forum = $topic->getParent();

    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'view')->isValid() ) {
      return;
    }

    // Settings
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->post_id = $post_id = (int) $this->_getParam('post_id');
    $this->view->decode_bbcode = $settings->getSetting('forum_bbcode');
    
    // Views
    if( !$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id ) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    // Check watching
    $isWatching = null;
    if( $viewer->getIdentity() ) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'forum');
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( false === $isWatching ) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    $this->view->isWatching = $isWatching;
    
    // Auth
    $canPost = false;
    $canEdit = false;
    $canDelete = false;
    if( !$topic->closed && Engine_Api::_()->authorization()->isAllowed($forum, null, 'post.create') ) {
      $canPost = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.edit') ) {
      $canEdit = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.delete') ) {
      $canDelete = true;
    }
    $this->view->canPost = $canPost;
    $this->view->canEdit = $canEdit;
    $this->view->canDelete = $canDelete;

    // Make form
    if( $canPost ) {
      $this->view->form = $form = new Forum_Form_Post_Quick();
      $form->setAction($topic->getHref(array('action' => 'post-create')));
      $form->populate(array(
        'topic_id' => $topic->getIdentity(),
        'ref' => $topic->getHref(),
        'watch' => ( false === $isWatching ? '0' : '1' ),
      ));
    }

    // Keep track of topic user views to show them which ones have new posts
    if( $viewer->getIdentity() ) {
      $topic->registerView($viewer);
    }
    
    $table = Engine_Api::_()->getItemTable('forum_post');
    $select = $topic->getChildrenSelect('forum_post', array('order'=>'post_id ASC'));
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($settings->getSetting('forum_topic_pagelength'));

    // set up variables for pages
    $page_param = (int) $this->_getParam('page');
    $post = Engine_Api::_()->getItem('forum_post', $post_id);
            
    // if there is a post_id
    if( $post_id && $post && !$page_param )
    {
      $icpp = $paginator->getItemCountPerPage();
      $post_page = ceil(($post->getPostIndex() + 1) / $icpp);

      $paginator->setCurrentPageNumber($post_page);
    }
    // Use specified page
    else if( $page_param )
    {      
      $paginator->setCurrentPageNumber($page_param);
    }
 }


 public function stickyAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit') ) {
      return;
    }
    
    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->sticky = ( null === $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
      $topic->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }

  public function closeAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit') ) {
      return;
    }
    
    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->closed = ( null === $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
      $topic->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }

  public function renameAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit') ) {
      return;
    }
    
    $this->view->form = $form = new Forum_Form_Topic_Rename();

    if( !$this->getRequest()->isPost() )
    {
      $form->title->setValue(htmlspecialchars_decode(($topic->title)));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $title = htmlspecialchars($form->getValue('title'));
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->title = $title;
      $topic->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic renamed.')),
      'layout' => 'default-simple',
      'parentRefresh' => true,
    ));
  }

  public function moveAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit') ) {
      return;
    }

    $this->view->form = $form = new Forum_Form_Topic_Move();

    // Populate with options
    $multiOptions = array();
    foreach( Engine_Api::_()->getItemTable('forum')->fetchAll() as $forum ) {
      $multiOptions[$forum->getIdentity()] = $this->view->translate($forum->getTitle());
    }
    $form->getElement('forum_id')->setMultiOptions($multiOptions);

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      // Update topic
      $topic->forum_id = $values['forum_id'];
      $topic->save();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic moved.')),
      'layout' => 'default-simple',
      //'parentRefresh' => true,
      'parentRedirect' => $topic->getHref(),
    ));
  }
  
  public function postCreateAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'post.create')->isValid() ) {
      return;
    }
    if( $topic->closed ) {
      return;
    }
    
    $this->view->form = $form = new Forum_Form_Post_Create();

    $quote_id = $this->getRequest()->getParam('quote_id');
    if( !empty($quote_id) ) {
      $quote = Engine_Api::_()->getItem('forum_post', $quote_id);
      if($quote->user_id == 0) {
          $owner_name = Zend_Registry::get('Zend_Translate')->_('Deleted Member');
      } else {
          $owner_name = $quote->getOwner()->__toString();
      }
      $form->body->setValue("<blockquote><strong>" . $this->view->translate('%1$s said:', $owner_name) . "</strong><br />" . $quote->body . "</blockquote><br />");
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();
    $values['body'] = $values['body'];
    $values['user_id'] = $viewer->getIdentity();
    $values['topic_id'] = $topic->getIdentity();
    $values['forum_id'] = $forum->getIdentity();

    $topicTable = Engine_Api::_()->getDbtable('topics', 'forum');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'forum');
    $postTable = Engine_Api::_()->getDbtable('posts', 'forum');
    $userTable = Engine_Api::_()->getItemTable('user');
    $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

    $viewer = Engine_Api::_()->user()->getViewer();
    $topicOwner = $topic->getOwner();
    $isOwnTopic = $viewer->isSelf($topicOwner);

    $watch = (bool) $values['watch'];
    $isWatching = $topicWatchesTable
      ->select()
      ->from($topicWatchesTable->info('name'), 'watch')
      ->where('resource_id = ?', $forum->getIdentity())
      ->where('topic_id = ?', $topic->getIdentity())
      ->where('user_id = ?', $viewer->getIdentity())
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;
    
    $db = $postTable->getAdapter();
    $db->beginTransaction();

    try {

      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();
      
      if( $values['photo'] ) {
        try {
          $post->setPhoto($form->photo);
        } catch( Engine_Image_Adapter_Exception $e ) {}
      }

      // Watch
      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $forum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $forum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      // Activity
      $action = $activityApi->addActivity($viewer, $topic, 'forum_topic_reply');
      if( $action ) {
        $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
      }

      // Notifications
      $notifyUserIds = $topicWatchesTable->select()
        ->from($topicWatchesTable->info('name'), 'user_id')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('watch = ?', 1)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN)
        ;

      foreach( $userTable->find($notifyUserIds) as $notifyUser ) {
        // Don't notify self
        if( $notifyUser->isSelf($viewer) ) {
          continue;
        }
        if( $notifyUser->isSelf($topicOwner) ) {
          $type = 'forum_topic_response';
        } else {
          $type = 'forum_topic_reply';
        }
        $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
          'message' => $this->view->BBCode($post->body), // @todo make sure this works
        ));
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    
    return $this->_redirectCustom($post);
  }

  public function watchAction()
  {
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $this->view->forum = $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, $viewer, 'view')->isValid() ) {
      return;
    }

    $watch = $this->_getParam('watch', true);

    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'forum');
    $db = $topicWatchesTable->getAdapter();
    $db->beginTransaction();

    try
    {
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;

      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $forum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $forum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }
}