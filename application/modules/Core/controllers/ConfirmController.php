<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ConfirmController.php 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_ConfirmController extends Engine_Controller_Action
{
  public function confirmAction()
  {
    $params = $this->getRequest()->getParams();
    $this->view->confirm_route = $params['confirm_route'];
    $this->view->deny_route = $params['deny_route'];
    $this->view->args = $params;
    $this->view->base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
    $this->view->confirm_text = $params['confirm_text'];
  }
}