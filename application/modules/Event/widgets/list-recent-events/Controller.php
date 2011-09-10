<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8427 2011-02-09 23:11:24Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Widget_ListRecentEventsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Should we consider creation or modified recent?
    $recentType = $this->_getParam('recentType', 'creation');
    if( !in_array($recentType, array('creation', 'modified', 'start', 'end')) ) {
      $recentType = 'creation';
    }
    $this->view->recentType = $recentType;
    if( in_array($recentType, array('start', 'end')) ) {
      $this->view->recentCol = $recentCol = $recentType . 'time';
    } else {
      $this->view->recentCol = $recentCol = $recentType . '_date';
    }
    
    // Get paginator
    $table = Engine_Api::_()->getItemTable('event');
    $select = $table->select()
      ->where('search = ?', 1);
    if( $recentType == 'creation' ) {
      // using primary should be much faster, so use that for creation
      $select->order('event_id DESC');
    } else {
      $select->order($recentCol . ' DESC');
    }
    // If start or end, filter by < now
    if( $recentType == 'start' ) {
      $select->where('starttime < ?', new Zend_Db_Expr('NOW()'));
    } else if( $recentType == 'end' ) {
      $select->where('endtime < ?', new Zend_Db_Expr('NOW()'));
    }
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Hide if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}