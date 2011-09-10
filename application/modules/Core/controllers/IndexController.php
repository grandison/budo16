<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: IndexController.php 8091 2010-12-21 02:20:59Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    if( Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // check public settings
    if( !Engine_Api::_()->getApi('settings', 'core')->core_general_portal &&
        !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled()
        ;
  }
}