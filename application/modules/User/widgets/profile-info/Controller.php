<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7703 2010-10-26 02:56:29Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Widget_ProfileInfoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('user');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      return $this->setNoRender();
    }

    // Member type
    $subject = Engine_Api::_()->core()->getSubject();
    $fieldsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($subject);

    if( !empty($fieldsByAlias['profile_type']) )
    {
      $optionId = $fieldsByAlias['profile_type']->getValue($subject);
      if( $optionId ) {
        $optionObj = Engine_Api::_()->fields()
          ->getFieldsOptions($subject)
          ->getRowMatching('option_id', $optionId->value);
        if( $optionObj ) {
          $this->view->memberType = $optionObj->label;
        }
      }
    }

    // Networks
    $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($subject)
      ->where('hide = ?', 0);
    $this->view->networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
    
    // Friend count
    $this->view->friendCount = $subject->membership()->getMemberCount($subject);
  }
}