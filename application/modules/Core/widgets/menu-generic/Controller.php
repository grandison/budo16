<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Widget_MenuGenericController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $name = $this->_getParam('menu');
    if( !$name ) {
      return $this->setNoRender();
    }

    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation($name);

    if( count($navigation) <= 0 ) {
      return $this->setNoRender();
    }
    
    $this->view->ulClass = $this->_getParam('ulClass', null);
  }
}