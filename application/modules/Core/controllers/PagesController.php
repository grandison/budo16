<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PagesController.php 8091 2010-12-21 02:20:59Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_PagesController extends Core_Controller_Action_Standard
{
  public function __call($methodName, array $arguments)
  {
    // Not an action
    if( 'Action' != substr($methodName, -6) ) {
      throw new Zend_Controller_Action_Exception(sprintf('Method "%s" does not exist and was not trapped in __call()', $methodName), 500);
    }

    // Get page
    $action = substr($methodName, 0, strlen($methodName) - 6);

    // Have to un inflect
    if( is_string($action) ) {
      $actionNormal = strtolower(preg_replace('/([A-Z])/', '-\1', $action));
      // @todo This may be temporary
      $actionNormal = str_replace('-', '_', $actionNormal);
    }

    // Get page object
    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageSelect = $pageTable->select();

    if( is_numeric($actionNormal) ) {
      $pageSelect->where('page_id = ?', $actionNormal);
    } else {
      $pageSelect
        ->orWhere('name = ?', str_replace('-', '_', $actionNormal))
        ->orWhere('url = ?', str_replace('_', '-', $actionNormal));
    }

    $pageObject = $pageTable->fetchRow($pageSelect);

    // Page found
    if( null !== $pageObject ) {
      // Check if the viewer can view this page
      $viewer = Engine_Api::_()->user()->getViewer();
      if( $pageObject->custom && !$pageObject->allowedToView($viewer) ) {
        return $this->_forward('requireauth', 'error', 'core');
      }
      // Render the page
      $this->_helper->content
        ->setContentName($pageObject->page_id)
        ->setNoRender()
        ->setEnabled();
      return;
    }


    // Missing page
    throw new Zend_Controller_Action_Exception(sprintf('Action "%s" does not exist and was not trapped in __call()', $action), 404);
  }
}