<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8758 2011-03-30 23:50:30Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Widget_AdminStatisticsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // License info
    $this->view->site = Engine_Api::_()->getApi('settings', 'core')->core_site;
    $this->view->license = Engine_Api::_()->getApi('settings', 'core')->core_license;

    // check if it is a trial license
    if( !Core_Form_Admin_Settings_License::validateKey($this->view->license['key']) ) {
      $this->view->license['key'] = $this->view->htmlLink(array('action' => 'license-key'), $this->view->translate('Update License Key'), array('class' => 'smoothbox'));
    }

    // Get the core module version
    $this->view->coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->select()
      ->from('engine4_core_modules', 'version')
      ->where('name = ?', 'core')
      ->query()
      ->fetchColumn();

    // Statistics
    $statistics = array();
    
    // views
    $statistics['core.views'] = array(
      'label' => 'Page Views',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.views', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.views'),
    );

    // signups
    $statistics['user.creations'] = array(
      'label' => 'Members',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.creations', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.creations'),
    );

    // logins
    $statistics['user.logins'] = array(
      'label' => 'Sign-ins',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.logins', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.logins'),
    );

    // messages
    $statistics['messages.creations'] = array(
      'label' => 'Private Messages',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('messages.creations', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('messages.creations'),
    );

    // friendships
    // @todo this only works properly for two-way, verified friendships for now
    $statistics['user.friendships'] = array(
      'label' => 'Friendships',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.friendships', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('user.friendships'),
    );

    // comments
    // @todo this doesn't include activity feed, users, group, or events for now
    $statistics['core.comments'] = array(
      'label' => 'Comments',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.comments', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.comments'),
    );

    // reports
    $statistics['core.reports'] = array(
      'label' => 'Abuse Reports',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.reports', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.reports'),
    );

    // announcements
    $statistics['announcement.creations'] = array(
      'label' => 'Announcements',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('announcement.creations', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('announcement.creations'),
    );

    // emails
    $statistics['core.emails'] = array(
      'label' => 'Emails Sent',
      'today' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.emails', 'day'),
      'total' => Engine_Api::_()->getDbtable('statistics', 'core')->getTotal('core.emails'),
    );


    
    // Hooks
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onAdminStatistics');
    $statistics += (array) $event->getResponses();



    
    // Online users
    $onlineTable = Engine_Api::_()->getDbtable('online', 'user');
    $onlineUserCount = $onlineTable->select()
      ->from($onlineTable->info('name'), new Zend_Db_Expr('COUNT(DISTINCT user_id)'))
      ->where('user_id > ?', 0)
      ->where('active > ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 20 MINUTE)'))
      ->query()
      ->fetchColumn(0)
      ;
    
    $statistics['users.online'] = array(
      'label' => 'Online Members',
      'today' => null,
      'total' => $onlineUserCount,
    );



    // Assign
    $this->view->statistics = $statistics;
  }
}