<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminLevelController.php 7940 2010-12-05 20:10:31Z char $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_AdminLevelController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('forum_admin_main', array(), 'forum_admin_main_level');

    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;

    // Make form
    $this->view->form = $form = new Forum_Form_Admin_Settings_Level(array(
      'public' => ( in_array($level->type, array('public')) ),
      'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($level_id);

    // Prepare modified permission keys
    $permissionKeys = array_keys($form->getValues());
    $fixedPermissionKeys = array();
    foreach( $permissionKeys as $index => $key ) {
      if( strpos($key, '_') !== false ) {
        list($type, $subtype) = explode('_', $key);
        $fixedPermissionKeys[$type][] = $subtype;
      } else {
        $fixedPermissionKeys['forum'][] = $key;
      }
    }

    // Populate form
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $fixedPermissionValues = array();
    foreach( $fixedPermissionKeys as $type => $typeArray ) {
      if( $type == 'forum' ) {
        $typeKey = 'forum';
      } else {
        $typeKey = 'forum_' . $type;
      }
      
      $values = $permissionsTable->getAllowed($typeKey, $level_id, $typeArray);

      foreach( $values as $valueKey => $value ) {
        if( $type == 'forum' ) {
          $formKey = $valueKey;
        } else {
          $formKey = $type . '_' . $valueKey;
        }
        $fixedPermissionValues[$formKey] = $value;
      }
    }
    $form->populate($fixedPermissionValues);

    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();

    $fixedPermissionValues = array();
    foreach( $values as $key => $value ) {
      if( strpos($key, '_') === false ) {
        $fixedPermissionValues['forum'][$key] = $value;
      } else {
        list($type, $subtype) = explode('_', $key);
        $fixedPermissionValues['forum'][$type . '.' . $subtype] = $value;
        $fixedPermissionValues['forum_' . $type][$subtype] = $value;
      }
    }
    
    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try
    {
      foreach( $fixedPermissionValues as $type => $fixedValues ) {
        $permissionsTable->setAllowed($type, $level_id, $fixedValues);
      }

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }
}