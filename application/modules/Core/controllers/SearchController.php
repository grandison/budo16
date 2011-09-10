<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: SearchController.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_SearchController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    $searchApi = Engine_Api::_()->getApi('search', 'core');
    //$viewer = $this->_helper->api()->user()->getViewer();

    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_search;
    if( !$require_check ) {
      if( !$this->_helper->requireUser()->isValid() ) return;
    }

    // Prepare form
    $this->view->form = $form = new Core_Form_Search();

    // Get available types
    $availableTypes = $searchApi->getAvailableTypes();
    if( is_array($availableTypes) && count($availableTypes) > 0 ) {
      $options = array();
      foreach( $availableTypes as $index => $type ) {
        $options[$type] = strtoupper('ITEM_TYPE_' . $type);
      }
      $form->type->addMultiOptions($options);
    } else {
      $form->removeElement('type');
    }

    // Check form validity?
    $values = array();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    }
    
    $this->view->query = $query = (string) @$values['query'];
    $this->view->type = $type = (string) @$values['type'];
    $this->view->page = $page = (int) $this->_getParam('page');
    if( $query )
    {
      $this->view->paginator = $searchApi->getPaginator($query, $type);
      $this->view->paginator->setCurrentPageNumber($page);
    }
  }
}