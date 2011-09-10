<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Widget_HomePlaylistController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if( !$this->_getParam('playlist_id') ) {
      return $this->setNoRender();
    }

    $playlist = Engine_Api::_()->getItem('music_playlist', $this->_getParam('playlist_id'));
    if( !$playlist ) {
      return $this->setNoRender();
    }

    $this->view->playlist = $playlist;
    $this->view->owner = $owner = $playlist->getOwner();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
  }

  public function adminAction()
  {
    // Check auth
    if( !Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view') ) {
      return $this->setNoRender();
    }
    
    $this->view->form = $form = new Music_Form_Admin_Widget_HomePlaylist();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $this->view->values = $form->getValues();
  }
}