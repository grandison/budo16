<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8427 2011-02-09 23:11:24Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Widget_ShowAlsoLikedController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Check subject
    if( !Engine_Api::_()->core()->hasSubject('video') ) {
      return $this->setNoRender();
    }
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('video');


    // Set default title
    if( !$this->getElement()->getTitle() ) {
      $this->getElement()->setTitle('People Also Liked');
    }

    // Get likes
    $itemTable = Engine_Api::_()->getItemTable($subject->getType());
    $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
    $likesTableName = $likesTable->info('name');
    
    $select = $itemTable->select()
      ->distinct(true)
      ->from($itemTable)
      ->joinLeft($likesTableName, $likesTableName.'.resource_id=video_id', null)
      ->joinLeft($likesTableName . ' as l2', $likesTableName.'.poster_id=l2.poster_id', null)
      ->where($likesTableName . '.poster_type = ?', 'user')
      ->where('l2.poster_type = ?', 'user')
      ->where($likesTableName . '.resource_type = ?', $subject->getType())
      ->where('l2.resource_type = ?', $subject->getType())
      ->where($likesTableName . '.resource_id != ?', $subject->getIdentity())
      ->where('l2.resource_id = ?', $subject->getIdentity())
      ->where('search = ?', true)
      ->where('video_id != ?', $subject->getIdentity())
      //->order(new Zend_Db_Expr('COUNT(like_id)'))
      ;

    // Get paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Hide if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}