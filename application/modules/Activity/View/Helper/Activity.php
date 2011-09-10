<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Activity.php 8077 2010-12-17 01:22:33Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_View_Helper_Activity extends Zend_View_Helper_Abstract
{
  public function activity(Activity_Model_Action $action = null, array $data = array())
  {
    if( null === $action )
    {
      return '';
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')
        ->getAllowed('user', $viewer->level_id, 'activity');

    $form = new Activity_Form_Comment();
    $data = array_merge($data, array(
      'actions' => array($action),
      'commentForm' => $form,
      'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
      'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
      'activity_moderate' =>$activity_moderate,
    ));

    return $this->view->partial(
      '_activityText.tpl',
      'activity',
      $data
    );
  }
}