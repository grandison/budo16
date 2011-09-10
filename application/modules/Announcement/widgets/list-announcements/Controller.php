<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7644 2010-10-15 03:24:39Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Announcement_Widget_ListAnnouncementsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Get paginator
    $table = Engine_Api::_()->getDbtable('announcements', 'announcement');
    $select = $table->select()
      //->order('announcement_id DESC')
      ->order('creation_date DESC')
      ;

    $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 2));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Hide if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
    
    $this->view->announcements = $paginator;
  }
}