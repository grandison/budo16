<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8190 2011-01-11 00:18:46Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_IndexController extends Core_Controller_Action_Standard
{
  public function init()
  {
    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'view')->isValid()) {
      return;
    }

    // Get viewer info
    $this->view->viewer     = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id  = Engine_Api::_()->user()->getViewer()->getIdentity();
  }
  
  public function browseAction()
  {
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_quick');

    // Can create?
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('music_playlist', null, 'create');

    // Get browse params
    $this->view->formFilter = $formFilter = new Music_Form_Search();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    } else {
      $values = array();
    }
    $this->view->formValues = array_filter($values);

    // Show
    $viewer = Engine_Api::_()->user()->getViewer();
    if( @$values['show'] == 2 && $viewer->getIdentity() ) {
      // Get an array of friend ids
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    }
    unset($values['show']);

    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->music()->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('music.playlistsperpage', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
  public function manageAction()
  {
    // only members can manage music
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_main');

    // Get quick navigation
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_quick');

    // Can create?
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('music_playlist', null, 'create');
    
    // Get browse params
    $this->view->formFilter = $formFilter = new Music_Form_Search();
    $formFilter->removeElement('show');
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    } else {
      $values = array();
    }
    $this->view->formValues = array_filter($values);

    // Get paginator
    $values['user'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->paginator = $paginator = Engine_Api::_()->music()->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('music.playlistsperpage', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function createAction()
  {
    // only members can upload music
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'create')->isValid() ) {
      return;
    }

    // catch uploads from FLASH fancy-uploader and redirect to uploadSongAction()
    if( $this->getRequest()->getQuery('ul', false) ) {
      return $this->_forward('upload', 'song', null, array('format' => 'json'));
    }

    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('music_main');

    // Get form
    $this->view->form = $form = new Music_Form_Create();
    $this->view->playlist_id = $this->_getParam('playlist_id', '0');

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    try {
      $playlist = $this->view->form->saveValues();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }
    
    return $this->_helper->redirector->gotoUrl($playlist->getHref(), array('prependBase' => false));
  }
}