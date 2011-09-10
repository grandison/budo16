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
class Video_Widget_ShowSameTagsController extends Engine_Content_Widget_Abstract
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
      $this->getElement()->setTitle('Similar Videos');
    }

    // Get tags for this video
    $itemTable = Engine_Api::_()->getItemTable($subject->getType());
    $tagMapsTable = Engine_Api::_()->getDbtable('tagMaps', 'core');
    $tagsTable = Engine_Api::_()->getDbtable('tags', 'core');
    
    // Get tags
    $tags = $tagMapsTable->select()
      ->from($tagMapsTable, 'tag_id')
      ->where('resource_type = ?', $subject->getType())
      ->where('resource_id = ?', $subject->getIdentity())
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);

    // No tags
    if( empty($tags) ) {
      return $this->setNoRender();
    }

    // Get other with same tags
    $select = $itemTable->select()
      ->distinct(true)
      ->from($itemTable)
      ->joinLeft($tagMapsTable->info('name'), 'resource_id=video_id', null)
      ->where('resource_type = ?', $subject->getType())
      ->where('resource_id != ?', $subject->getIdentity())
      ->where('tag_id IN(?)', $tags)
      ->where('search = ?', true) // ?
      //->order()
      ;

    /*
    $ids = $tagMapsTable->select()
      ->from($tagMapsTable, 'resource_id')
      ->where('resource_type = ?', $subject->getType())
      ->where('resource_id != ?', $subject->getIdentity())
      ->where('tagmap_id IN(?)', $tagmaps);
      ->order('')
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
    */

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