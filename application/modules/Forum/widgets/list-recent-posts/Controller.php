<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Widget_ListRecentPostsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Get paginator
    $postsTable = Engine_Api::_()->getDbtable('posts', 'forum');
    $postsSelect = $postsTable->select()
      ->order('creation_date DESC')
      ;

    $this->view->paginator = $paginator = Zend_Paginator::factory($postsSelect);
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}