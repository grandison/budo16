<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8536 2011-03-01 04:43:10Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Widget_ListSignupsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $table->select()
      ->where('search = ?', 1)
      ->where('enabled = ?', 1)
      ->order('creation_date DESC')
      ;

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 4));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }

  public function getCacheKey()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $translate = Zend_Registry::get('Zend_Translate');
    return $viewer->getIdentity() . $translate->getLocale();
  }

  public function getCacheSpecificLifetime()
  {
    return 120;
  }
}