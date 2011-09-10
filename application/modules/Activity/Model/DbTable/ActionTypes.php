<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ActionTypes.php 8224 2011-01-15 02:02:16Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Model_DbTable_ActionTypes extends Engine_Db_Table
{
  protected $_actionTypes;
  
  /**
   * Gets all action type meta info
   *
   * @param string|null $type
   * @return Engine_Db_Rowset
   */
  public function getActionTypes()
  {
    if( null === $this->_actionTypes ) {
      // Only get enabled types
      //$this->_actionTypes = $this->fetchAll();
      $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
      $select = $this->select()
        ->where('module IN(?)', $enabledModuleNames)
        ;
      $this->_actionTypes = $this->fetchAll($select);
    }

    return $this->_actionTypes;
  }

  public function getActionTypeNames()
  {
    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    return $this->select()
      ->from($this->info('name'), 'type')
      ->where('module IN(?)', $enabledModuleNames)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
  }

  public function getEnabledActionTypeNames()
  {
    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction') == 1 ) {
      $exclude = 'friends_follow';
    } else {
      $exclude = 'friends';
    }
    return $this->select()
      ->from($this->info('name'), 'type')
      ->where('enabled = ?', 1)
      ->where('displayable > ?', 0)
      ->where('module IN(?)', $enabledModuleNames)
      ->where('type != ?', $exclude)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
  }

  public function getActionType($type)
  {
    return $this->getActionTypes()->getRowMatching('type', $type);
  }
  
  public function getActionTypesAssoc()
  {
    $arr = array();
    $translate = Zend_Registry::get('Zend_Translate');
    foreach( $this->getActionTypes() as $type ) {
      $arr[$type->type] = $translate->_('_ACTIVITY_ACTIONTYPE_'.strtoupper($type->type));
    }
    return $arr;
  }

  public function getEnabledActionTypesAssoc()
  {
    $arr = array();
    $translate = Zend_Registry::get('Zend_Translate');
    foreach( $this->getActionTypes() as $type ) {
      if( !$type->enabled || !$type->displayable ) continue;
      $arr[$type->type] = $translate->_('_ACTIVITY_ACTIONTYPE_' . strtoupper($type->type));
    }
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction') == 1 ) {
      unset($arr['friends_follow']);
    } else {
      unset($arr['friends']);
    }
    return $arr;
  }

  public function getEnabledGroupedActionTypes()
  {
    // Get enabled modules
    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction') == 1 ) {
      $exclude = 'friends_follow';
    } else {
      $exclude = 'friends';
    }

    // Get types
    $actionTypes = $this->select()
      ->from($this->info('name'), array('type', 'module'))
      ->where('enabled = ?', 1)
      ->where('displayable > ?', 0)
      ->where('module IN(?)', $enabledModuleNames)
      ->where('type != ?', $exclude)
      ->query()
      ->fetchAll()
      ;

    // Group them
    $groupedActionTypes = array('all' => null);
    foreach( $actionTypes as $actionType ) {
      // All
      //$groupedActionTypes['all'][] = $actionType['type'];
      // Photo
      if( false !== strpos($actionType['type'], 'photo') ) {
        $groupedActionTypes['photo'][] = $actionType['type'];
      }
      // Music
      if( false !== strpos($actionType['type'], 'music') ||
          false !== strpos($actionType['type'], 'song') ) {
        $groupedActionTypes['music'][] = $actionType['type'];
      }
      // Video
      if( false !== strpos($actionType['type'], 'video') ) {
        $groupedActionTypes['video'][] = $actionType['type'];
      }
      // Posts?
      if( false !== strpos($actionType['type'], 'comment') ||
          false !== strpos($actionType['type'], 'topic') ||
          false !== strpos($actionType['type'], 'post') ||
          false !== strpos($actionType['type'], 'status') ) {
        $groupedActionTypes['posts'][] = $actionType['type'];
      }
      // By module?
      $groupedActionTypes[$actionType['module']][] = $actionType['type'];
    }

    return $groupedActionTypes;
  }
}