<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminIndexController.php 8394 2011-02-04 01:06:41Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminIndexController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    
  }

  public function licenseKeyAction()
  {
    $form = $this->view->form = new Core_Form_Admin_Settings_License();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $db = Engine_Api::_()->getDbtable('settings', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        Engine_Api::_()->getApi('settings', 'core')->core_license_key = $form->getValue('key');
        $db->commit();

        $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefresh' => true,
          'format'=> 'smoothbox',
          'messages' => array(Zend_Registry::get('Zend_Translate')->_("License saved"))
        ));

      } catch (Exception $e) {
        $db->rollback();
        $form->getElement('key')->addError('There was a problem saving the new license key; please try again.');
        return;
      }
      
    }
  }

  public function changeEnvironmentModeAction()
  {
    if ($this->getRequest()->isPost() && $this->_getParam('environment_mode', false)) {
      $global_settings_file = APPLICATION_PATH . '/application/settings/general.php';
      if( file_exists($global_settings_file) ) {
        $g = include $global_settings_file;
        if (!is_array($g)) {
          $g = (array) $g;
        }
      } else {
        $g = array();
      }
      
      if (!is_writable($global_settings_file)) {
        // not writable; can we delete and re-create?
        if (is_writable(dirname($global_settings_file))) {
          @rename($global_settings_file, $global_settings_file.'_backup.php');
          @touch($global_settings_file);
          @chmod($global_settings_file, 0666);
          if (!file_exists($global_settings_file) || !is_writable($global_settings_file)) {
            @rename($global_settings_file, $global_settings_file.'_delete.php');
            @rename($global_settings_file.'_backup.php', $global_settings_file);
            @unlink($global_settings_file.'_delete.php');
          }
        }
        if (!is_writable($global_settings_file)) {
          $this->view->success = false;
          $this->view->error   = 'Unable to write to settings file; please CHMOD 666 the file /application/settings/general.php, then try again.';
          return;
        } else {
          // it worked; continue.
        }
      }

      if ($this->_getParam('environment_mode') != @$g['environment_mode']) {
        $g['environment_mode'] = $this->_getParam('environment_mode');
        $file_contents  = "<?php defined('_ENGINE') or die('Access Denied'); return ";
        $file_contents .= var_export($g, true);
        $file_contents .= "; ?>";
        $this->view->success = @file_put_contents($global_settings_file, $file_contents);

        // clear scaffold cache
        Core_Model_DbTable_Themes::clearScaffoldCache();

        // Increment site counter
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $settings->core_site_counter = $settings->core_site_counter + 1;

        return;
      } else {
        $this->view->message = 'No change necessary';
        $this->view->success = true; // no change
      }
    }
    $this->view->success = false;
    
  }
}