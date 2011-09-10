<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8366 2011-02-01 03:42:27Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Widget_ListRecentClassifiedsController extends Engine_Content_Widget_Abstract
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
    $table = Engine_Api::_()->getItemTable('classified');
    $select = $table->select()
      ->where('search = ?', 1);
    if( $recentType == 'creation' ) {
      // using primary should be much faster, so use that for creation
      $select->order('classified_id DESC');
    } else {
      $select->order($recentCol . ' DESC');
    }
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Hide if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }

    // Add fields view helper path
    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
  }
}