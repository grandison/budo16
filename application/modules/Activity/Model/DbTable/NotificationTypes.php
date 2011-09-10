<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: NotificationTypes.php 8572 2011-03-05 18:43:15Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Model_DbTable_NotificationTypes extends Engine_Db_Table
{
  /**
   * All notification types
   *
   * @var Engine_Db_Table_Rowset
   */
  protected $_notificationTypes;



  // Types

  /**
   * Gets action type meta info
   *
   * @param string $type
   * @return Engine_Db_Row
   */
  public function getNotificationType($type)
  {
    return $this->getNotificationTypes()->getRowMatching('type', $type);
  }

  /**
   * Gets all action type meta info
   *
   * @param string|null $type
   * @return Engine_Db_Rowset
   */
  public function getNotificationTypes()
  {
    if( null === $this->_notificationTypes )
    {
      // Only get enabled types
      //$this->_notificationTypes = $this->fetchAll();
      $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
      $select = $this->select()
        ->where('module IN(?)', $enabledModuleNames)
        ;

      // Exclude disabled friend types
      $excludedTypes = array();
      $friend_verfication = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.verification', true);
      $friend_direction = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', true);
      if( $friend_direction ) {
        $excludedTypes = array_merge($excludedTypes, array('friend_follow', 'friend_follow_accepted', 'friend_follow_request'));
      } else {
        $excludedTypes = array_merge($excludedTypes, array('friend_accepted', 'friend_request'));
      }
      if( !$friend_verfication ) {
        $excludedTypes = array_merge($excludedTypes, array('friend_follow_request', 'friend_request'));
      }
      if( !empty($excludedTypes) ) {
        $excludedTypes = array_unique($excludedTypes);
        $select->where('type NOT IN(?)', $excludedTypes);
      }

      // Gotta catch em' all
      $this->_notificationTypes = $this->fetchAll($select);
    }

    return $this->_notificationTypes;
  }

  /**
   * Get an assoc types type=>label
   *
   * @return array
   */
  public function getNotificationTypesAssoc()
  {
    $arr = array();
    $translate = Zend_Registry::get('Zend_Translate');
    
    foreach( $this->getNotificationTypes() as $type )
    {
      $arr[$type->type] = $translate->_('ACTIVITY_TYPE_'.strtoupper($type->type));
    }
    
    return $arr;
  }
  
  public function getDefaultNotifications()
  {
    
    $select = $this->select()
      ->from($this->info('name'), 'type')
      ->where('`default` = ?', 1);

    // Exclude disabled friend types
    $excludedTypes = array();
    $friend_verfication = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.verification', true);
    $friend_direction = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', true);
    if( $friend_direction ) {
      $excludedTypes = array_merge($excludedTypes, array('friend_follow', 'friend_follow_accepted', 'friend_follow_request'));
    } else {
      $excludedTypes = array_merge($excludedTypes, array('friend_accepted', 'friend_request'));
    }
    if( !$friend_verfication ) {
      $excludedTypes = array_merge($excludedTypes, array('friend_follow_request', 'friend_request'));
    }
      
    if( !empty($excludedTypes) ) {
      $excludedTypes = array_unique($excludedTypes);
      $select->where('type NOT IN(?)', $excludedTypes);
    }
    
    $types = $select
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
      
    return $types;
  }
  
  public function setDefaultNotifications($values)
  {
    if( !is_array($values) ){
      throw new Activity_Model_Exception('setDefaultNotifications requires an array of notifications');
    }

    $types = $this->select()
      ->from($this->info('name'), 'type')
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;

    $defaults = array();
    foreach( $types as $value ){
      if( in_array($value, $values) ){
        $defaults[] = $value;
      }
    }
    
    if( !empty($defaults) ){
      
      $this->update(
        array('default' => '1',), 
        array('`type` IN(?)' => $defaults));
        
      $this->update(
        array('default' => '0',), 
        array('`type` NOT IN(?)' => $defaults));
        
    } else {
      $this->update(array('default' => '0'), array('`default`' => '1'));
    }

  }
}