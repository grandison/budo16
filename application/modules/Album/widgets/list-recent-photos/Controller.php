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
class Album_Widget_ListRecentPhotosController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Should we consider creation or modified recent?
    $recentType = $this->_getParam('recentType', 'creation');
    if( !in_array($recentType, array('creation', 'modified')) ) {
      $recentType = 'creation';
    }
    $this->view->recentType = $recentType;
    $this->view->recentCol = $recentCol = $recentType . '_date';
    
    // Get paginator
    $parentTable = Engine_Api::_()->getItemTable('album');
    $table = Engine_Api::_()->getItemTable('album_photo');
    $select = $table->select()
      ->from($table->info('name'))
      ->joinLeft($parentTable->info('name'), 'collection_id=album_id', null)
      ->where('search = ?', true);
    if( $recentType == 'creation' ) {
      // using primary should be much faster, so use that for creation
      $select->order('photo_id DESC');
    } else {
      $select->order($table->info('name') . '.' . $recentCol . ' DESC');
    }
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