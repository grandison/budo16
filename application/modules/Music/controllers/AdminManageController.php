<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminManageController.php 7991 2010-12-08 18:17:43Z char $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_AdminManageController extends Core_Controller_Action_Admin
{
  public function init()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_admin_main', array(), 'music_admin_main_manage');
  }
  
  public function indexAction()
  {

    if ($this->getRequest()->isPost())
    {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key=>$value) {
        if ($key == 'delete_' . $value)
        {
          $playlist = Engine_Api::_()->getItem('music_playlist', $value);
          $playlist->delete();
        }
      }
    }
    $page = $this->_getParam('page',1);
    $this->view->paginator = Engine_Api::_()->music()->getPlaylistPaginator();
    $this->view->paginator->setItemCountPerPage(25);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function suggestAction()
  {
    $page = $this->_getParam('page');
    $query = $this->_getParam('query');

    $playlistTable = Engine_Api::_()->getItemTable('music_playlist');
    $playlistSelect = $playlistTable->select()
      ->where('title LIKE ?', '%' . $query . '%');
    $paginator = Zend_Paginator::factory($playlistSelect);
    $paginator->setCurrentPageNumber($page);

    $data = array();
    foreach( $paginator as $playlist ) {
      $data[$playlist->playlist_id] = $playlist->getTitle();
    }
    $this->view->status = true;
    $this->view->data = $data;
  }

  public function infoAction()
  {
    $playlistIdentity = $this->_getParam('playlist_id');
    if( !$playlistIdentity ) {
      $this->view->status = false;
      return;
    }

    $playlist = Engine_Api::_()->getItem('music_playlist', $playlistIdentity);
    if( !$playlist ) {
      $this->view->status = false;
      return;
    }

    $this->view->status = true;
    $this->view->identity = $playlist->getIdentity();
    $this->view->title = $playlist->getTitle();
    $this->view->description = $playlist->getDescription();
    $this->view->href = $playlist->getHref();
    $this->view->photo = $playlist->getPhotoUrl('thumb.icon');
  }

  public function deleteAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->playlist_id=$id;
    // Check post
    if( $this->getRequest()->isPost() )
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try
      {
        $playlist = Engine_Api::_()->getItem('music_playlist', $id);
        // delete the playlist into the database
        $playlist->delete();
        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-manage/delete.tpl');
  }
}