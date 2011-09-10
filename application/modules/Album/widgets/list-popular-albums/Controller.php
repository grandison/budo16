<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8427 2011-02-09 23:11:24Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Widget_ListPopularAlbumsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Should we consider views or comments popular?
    $popularType = $this->_getParam('popularType', 'comment');
    if( !in_array($popularType, array('comment', 'view')) ) {
      $popularType = 'comment';
    }
    $this->view->popularType = $popularType;
    $this->view->popularCol = $popularCol = $popularType . '_count';

    // Get paginator
    $table = Engine_Api::_()->getItemTable('album');
    $select = $table->select()
      ->where('search = ?', true)
      ->order($popularCol . ' DESC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}