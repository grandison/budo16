<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AjaxController.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_AjaxController extends Core_Controller_Standard
{
  public function suggestAction()
  {
    // Requires user
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get params
    $text = $this->_getParam('text', $this->_getParam('search', $this->_getParam('value')));
    $limit = (int) $this->_getParam('limit', 10);
    $offset = (int) $this->_getParam('offset', 0);
    $friends = (bool) $this->_getParam('friends', true);

    // Generate query
    if( $friends ) {
      // Friends only
      $select = $this->_helper->api()->user()->getViewer()->membership()->getMembersObjectSelect();
    } else {
      // Searchable users only
      $select = Engine_Api::_()->getItemTable('user')->select()->where('search = ?', 1);
    }

    if( null !== $text ) {
      $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
    }

    $select->limit($limit, $offset);

    // Retv data
    $data = array();
    foreach( $select->getTable()->fetchAll($select) as $friend )
    {
      $data[] = array(
        'id' => $friend->getIdentity(),
        'label' => $friend->getTitle(), // We should recode this to use title instead of label
        'title' => $friend->getTitle(),
        'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
        'url' => $friend->getHref(),
      );
    }

    // send data
    if( $this->_getParam('sendNow', true) )
    {
      return $this->_helper->json($data);
    }
    else
    {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
}