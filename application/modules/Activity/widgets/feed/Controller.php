<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8933 2011-05-12 20:37:57Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Widget_FeedController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = null;
    if( Engine_Api::_()->core()->hasSubject() ) {
      // Get subject
      $subject = Engine_Api::_()->core()->getSubject();
      if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
        return $this->setNoRender();
      }
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');

    // Get some options
    $this->view->feedOnly         = $feedOnly = $request->getParam('feedOnly', false);
    $this->view->length           = $length = $request->getParam('limit', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
    $this->view->itemActionLimit  = $itemActionLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.userlength', 5);

    $this->view->updateSettings   = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.liveupdate');
    $this->view->viewAllLikes     = $request->getParam('viewAllLikes',    $request->getParam('show_likes',    false));
    $this->view->viewAllComments  = $request->getParam('viewAllComments', $request->getParam('show_comments', false));
    $this->view->getUpdate        = $request->getParam('getUpdate');
    $this->view->checkUpdate      = $request->getParam('checkUpdate');
    $this->view->action_id        = (int) $request->getParam('action_id');
    $this->view->post_failed      = (int) $request->getParam('pf');
    
    if( $feedOnly ) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    if( $length > 50 ) {
      $this->view->length = $length = 50;
    }
    
    // Get all activity feed types for custom view?
//    $actionTypesTable = Engine_Api::_()->getDbtable('actionTypes', 'activity');
//    $this->view->groupedActionTypes = $groupedActionTypes = $actionTypesTable->getEnabledGroupedActionTypes();
//    $actionTypeGroup = $request->getParam('actionFilter');
//    $actionTypeFilters = array();
//    if( $actionTypeGroup && isset($groupedActionTypes[$actionTypeGroup]) ) {
//      $actionTypeFilters = $groupedActionTypes[$actionTypeGroup];
//    }
    
    // Get config options for activity
    $config = array(
      'action_id' => (int) $request->getParam('action_id'),
      'max_id'    => (int) $request->getParam('maxid'),
      'min_id'    => (int) $request->getParam('minid'),
      'limit'     => (int) $length,
      //'showTypes' => $actionTypeFilters,
    );

    // Pre-process feed items
    $selectCount = 0;
    $nextid = null;
    $firstid = null;
    $tmpConfig = $config;
    $activity = array();
    $endOfFeed = false;

    $friendRequests = array();
    $itemActionCounts = array();
    $enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();

    do {
      // Get current batch
      $actions = null;
      if( !empty($subject) ) {
        $actions = $actionTable->getActivityAbout($subject, $viewer, $tmpConfig);
      } else {
        $actions = $actionTable->getActivity($viewer, $tmpConfig);
      }
      $selectCount++;

      // Are we at the end?
      if( count($actions) < $length || count($actions) <= 0 ) {
        $endOfFeed = true;
      }

      // Pre-process
      if( count($actions) > 0 ) {
        foreach( $actions as $action ) {
          // get next id
          if( null === $nextid || $action->action_id <= $nextid ) {
            $nextid = $action->action_id - 1;
          }
          // get first id
          if( null === $firstid || $action->action_id > $firstid ) {
            $firstid = $action->action_id;
          }
          // skip disabled actions
          if( !$action->getTypeInfo() || !$action->getTypeInfo()->enabled ) continue;
          // skip items with missing items
          if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
          if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
          // track/remove users who do too much (but only in the main feed)
          if( empty($subject) ) {
            $actionSubject = $action->getSubject();
            $actionObject = $action->getObject();
            if( !isset($itemActionCounts[$actionSubject->getGuid()]) ) {
              $itemActionCounts[$actionSubject->getGuid()] = 1;
            } else if( $itemActionCounts[$actionSubject->getGuid()] >= $itemActionLimit ) {
              continue;
            } else {
              $itemActionCounts[$actionSubject->getGuid()]++;
            }
          }
          // remove duplicate friend requests
          if( $action->type == 'friends' ) {
            $id = $action->subject_id . '_' . $action->object_id;
            $rev_id = $action->object_id . '_' . $action->subject_id;
            if( in_array($id, $friendRequests) || in_array($rev_id, $friendRequests) ) {
              continue;
            } else {
              $friendRequests[] = $id;
              $friendRequests[] = $rev_id;
            }
          }

          // remove items with disabled module attachments
          try {
            $attachments = $action->getAttachments();
          } catch (Exception $e) {
            // if a module is disabled, getAttachments() will throw an Engine_Api_Exception; catch and continue
            continue;
          }
          
          // add to list
          if( count($activity) < $length ) {
            $activity[] = $action;
            if( count($activity) == $length ) {
              $actions = array();
            }
          }
        }
      }
      
      // Set next tmp max_id
      if( $nextid ) {
        $tmpConfig['max_id'] = $nextid;
      }
      if( !empty($tmpConfig['action_id']) ) {
        $actions = array();
      }
    } while( count($activity) < $length && $selectCount <= 3 && !$endOfFeed );

    $this->view->activity = $activity;
    $this->view->activityCount = count($activity);
    $this->view->nextid = $nextid;
    $this->view->firstid = $firstid;
    $this->view->endOfFeed = $endOfFeed;


    // Get some other info
    if( !empty($subject) ) {
      $this->view->subjectGuid = $subject->getGuid(false);
    }
    
    $this->view->enableComposer = false;
    if( $viewer->getIdentity() && !$this->_getParam('action_id') ) {
      if( !$subject || $subject->authorization()->isAllowed($viewer, 'comment') ) {
        $this->view->enableComposer = true;
      }
    }

    // Assign the composing values
    $composePartials = array();
    foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
      if( empty($data['composer']) ) {
        continue;
      }
      foreach( $data['composer'] as $type => $config ) {
        if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
          continue;
        }
        $composePartials[] = $config['script'];
      }
    }
    $this->view->composePartials = $composePartials;
  }
}
