<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Widget_AdminDashboardController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Users
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = new Zend_Db_Select($userTable->getAdapter());
    $select->from($userTable->info('name'), 'COUNT(user_id) as count');
    $data = $select->query()->fetch();
    $this->view->userCount = (int) $data['count'];

    // Reports
    $reportTable = Engine_Api::_()->getDbtable('reports', 'core');
    $select = new Zend_Db_Select($reportTable->getAdapter());
    $select->from($reportTable->info('name'), 'COUNT(report_id) as count')->where('`read` = ?', 0);
    $data = $select->query()->fetch();
    $this->view->reportCount = (int) $data['count'];

    // Plugins
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    $select = new Zend_Db_Select($moduleTable->getAdapter());
    $select->from($moduleTable->info('name'), 'COUNT(TRUE) as count')->where('type = ?', 'extra');
    $data = $select->query()->fetch();
    $this->view->pluginCount = (int) $data['count'];


    // Notifications
    // Hook-based
    $event = Engine_Hooks_Dispatcher::_()->callEvent('getAdminNotifications');
    $this->view->notifications = $event->getResponses();
    // Database-based
    $select = Engine_Api::_()->getDbtable('log', 'core')->select()
      ->where('domain = ?', 'admin')
      ->order('timestamp DESC')
      ;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(4);
  }
}