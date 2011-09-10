<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSettingsController.php 8935 2011-05-13 00:57:19Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminSettingsController extends Core_Controller_Action_Admin
{

  public function generalAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Settings_General();

    // Get settings
    $global_settings_file = APPLICATION_PATH . '/application/settings/general.php';
    if( file_exists($global_settings_file) ) {
      $generalConfig = include $global_settings_file;
    } else {
      $generalConfig = array();
    }

    // Populate form
    $form->populate($this->_helper->api()->getApi('settings', 'core')->getFlatSetting('core_general', array()));
    $form->populate(array(
      'maintenance_mode' => !empty($generalConfig['maintenance']['enabled']),
      'maintenance_code' => ( !empty($generalConfig['maintenance']['code']) ? $generalConfig['maintenance']['code'] : $this->_createRandomPassword(5) ),
    ));
    
    // Check post/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process form
    $values = $form->getValues();
    $maintenance = $values['maintenance_mode'];
    $maintenanceCode = $values['maintenance_code'];
    unset($values['maintenance_mode']);
    unset($values['maintenance_code']);
    if( empty($maintenanceCode) ) {
      $maintenanceCode = $this->_createRandomPassword(5);
      $form->populate(array(
        'maintenance_code' => $maintenanceCode,
      ));
    }

    // Save settings
    Engine_Api::_()->getApi('settings', 'core')->core_general = $values;

    // Save public level view permission
    $publicLevel = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
    Engine_Api::_()->authorization()->levels->setAllowed('user', $publicLevel, 'view', (bool) $values['profile']);

    // Save maintenance mode
    $generalConfig['maintenance']['enabled'] = (bool) $maintenance;
    $generalConfig['maintenance']['code'] = $maintenanceCode;
    if( $generalConfig['maintenance']['enabled'] ) {
      setcookie('en4_maint_code', $generalConfig['maintenance']['code'], time() + (60 * 60 * 24 * 365), $this->view->baseUrl());
    }
    
    if( (is_file($global_settings_file) && is_writable($global_settings_file)) ||
        (is_dir(dirname($global_settings_file)) && is_writable(dirname($global_settings_file))) ) {
      $file_contents = "<?php defined('_ENGINE') or die('Access Denied'); return ";
      $file_contents .= var_export($generalConfig, true);
      $file_contents .= "; ?>";
      file_put_contents($global_settings_file, $file_contents);
      $form->addNotice('Your changes have been saved.');
    } else {
      return $form->getElement('maintenance_mode')
          ->addError('Unable to configure this setting due to the file /application/settings/general.php not having the correct permissions.
                       Please CHMOD (change the permissions of) that file to 666, then try again.');
    }

  }

  public function localeAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Settings_Locale();

    // Save
    if( $this->getRequest()->isPost() ) {
      if( $form->isValid($this->getRequest()->getPost()) ) {
        $this->_helper->api()->getApi('settings', 'core')->core_locale = $form->getValues();
        $form->addNotice('Your changes have been saved.');
      }
    }

    // Initialize
    else {
      $form->populate($this->_helper->api()->getApi('settings', 'core')->core_locale);
    }
  }

  public function spamAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('core_admin_banning', array(), 'core_admin_banning_general');

    // Get form
    $this->view->form = $form = new Core_Form_Admin_Settings_Spam();

    // Populate some settings
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $form->populate((array) $settings->core_spam);

    // Get db 
    $db = Engine_Db_Table::getDefaultAdapter();

    // Load all IPs
    $bannedIps = $db->select()
      ->from('engine4_core_bannedips')
      ->query()
      ->fetchAll();
    $bannedIpsAssoc = array();
    if( !empty($bannedIps) ) {
      $str = '';
      foreach( $bannedIps as $bannedIp ) {
        // Add to index
        $bannedIpsAssoc[$bannedIp['start'] . '-' . $bannedIp['stop']] = $bannedIp['bannedip_id'];
        // Generate string
        if( $bannedIp['start'] == $bannedIp['stop'] ) {
          $str .= long2ip($bannedIp['start']) . "\n";
        } else {
          $str .= long2ip($bannedIp['start']) . '-'
            . long2ip($bannedIp['stop']) . "\n";
        }
      }
      $str = rtrim($str, ', ');
      if( $str !== '' ) {
        $form->populate(array(
          'ipbans' => $str,
        ));
      }
    }

    // Load all emails
    $bannedEmails = $db->select()
      ->from('engine4_core_bannedemails')
      ->query()
      ->fetchAll();
    $bannedEmailsAssoc = array();
    if( !empty($bannedEmails) ) {
      foreach( $bannedEmails as $bannedEmail ) {
        $bannedEmailsAssoc[$bannedEmail['email']] = $bannedEmail['bannedemail_id'];
      }
      $form->populate(array(
        'emailbans' => join("\n", array_keys($bannedEmailsAssoc)),
      ));
    }

    // Load all words
    $bannedWords = $db->select()
      ->from('engine4_core_bannedwords')
      ->query()
      ->fetchAll();
    $bannedWordsAssoc = array();
    if( !empty($bannedWords) ) {
      foreach( $bannedWords as $bannedWord ) {
        $bannedWordsAssoc[$bannedWord['word']] = $bannedWord['bannedword_id'];
      }
      $form->populate(array(
        'censor' => join("\n", array_keys($bannedWordsAssoc)),
      ));
    }
      
    
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $db = Engine_Api::_()->getDbtable('settings', 'core')->getAdapter();
    $db->beginTransaction();
    try {

      $values = $form->getValues();


      // Banned IPs
      if( !empty($values['ipbans']) ) {
        $newBannedIps = preg_split('/\s*[,\n]+\s*/', $values['ipbans']);
        $newBannedIpsAssoc = array();
        foreach( $newBannedIps as $newBannedIp ) {
          if( false !== strpos($newBannedIp, '-') ) {
            // Range
            list($start, $stop) = explode('-', $newBannedIp);
          } else if( false !== strpos($newBannedIp, '*') ) {
            // Star
            $start = str_replace('*', '0', $newBannedIp);
            $stop = str_replace('*', '255', $newBannedIp);
          } else {
            // Simple
            $start = $newBannedIp;
            $stop = $newBannedIp;
          }
          $start = ip2long($start);
          $stop = ip2long($stop);
          if( false === $start || false === $stop ) {
            // Skip invalid IPs?
            continue;
          }
          $key = $start . '-' . $stop;
          $newBannedIpsAssoc[$key] = array(
            'bannedip_id' => ( isset($bannedIpsAssoc[$key]) ? $bannedIpsAssoc[$key] : null ),
            'start' => $start,
            'stop' => $stop,
          );
        }
        // Delete
        foreach( $bannedIpsAssoc as $key => $bannedIp ) {
          if( !isset($newBannedIpsAssoc[$key]) ) {
            $db->delete('engine4_core_bannedips', array(
              'bannedip_id = ?' => $bannedIp,
            ));
          }
        }
        // Insert
        foreach( $newBannedIpsAssoc as $key => $bannedIp ) {
          if( !isset($bannedIpsAssoc[$key]) ) {
            $db->insert('engine4_core_bannedips', array(
              'start' => $bannedIp['start'],
              'stop' => $bannedIp['stop'],
            ));
          }
        }
      }
      // else empty the censor table
      else {
        $db->delete('engine4_core_bannedips');
      }
      // Remove key
      unset($values['ipbans']);

      // Banned Emails
      if( !empty($values['emailbans']) ) {
        $newBannedEmails = preg_split('/\s*[,\n]+\s*/', $values['emailbans']);
        $newBannedEmailsAssoc = array();
        foreach( $newBannedEmails as $newBannedEmail ) {
          $newBannedEmailsAssoc[$newBannedEmail] = $newBannedEmail;
        }
        // Delete
        foreach( $bannedEmailsAssoc as $key => $bannedEmailId ) {
          if( !isset($newBannedEmailsAssoc[$key]) ) {
            $db->delete('engine4_core_bannedemails', array(
              'bannedemail_id = ?' => $bannedEmailId,
            ));
          }
        }
        // Insert
        foreach( $newBannedEmailsAssoc as $key => $null ) {
          if( !isset($bannedEmailsAssoc[$key]) ) {
            $db->insert('engine4_core_bannedemails', array(
              'email' => $key,
            ));
          }
        }
      }
      // else empty the censor table
      else {
        $db->delete('engine4_core_bannedemails');
      }
      // Remove key
      unset($values['emailbans']);

      // Banned Words
      if( !empty($values['censor']) ) {
        $newBannedWords = preg_split('/\s*[,\n]+\s*/', $values['censor']);
        $newBannedWordsAssoc = array();
        foreach( $newBannedWords as $newBannedWord ) {
          $newBannedWordsAssoc[$newBannedWord] = $newBannedWord;
        }
        // Delete
        foreach( $bannedWordsAssoc as $key => $bannedWordId ) {
          if( !isset($newBannedWordsAssoc[$key]) ) {
            $db->delete('engine4_core_bannedwords', array(
              'bannedword_id = ?' => $bannedWordId,
            ));
          }
        }
        // Insert
        foreach( $newBannedWordsAssoc as $key => $null ) {
          if( !isset($bannedWordsAssoc[$key]) ) {
            $db->insert('engine4_core_bannedwords', array(
              'word' => $key,
            ));
          }
        }
      }
      // else empty the censor table
      else {
        $db->delete('engine4_core_bannedwords');
      }
      // Remove key
      unset($values['censor']);

      
      // Save other settings
      $settings->core_spam = $values;

      
      $db->commit();
      $form->addNotice('Your changes have been saved.');
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }
      
    if( $settings->core_spam ) {
      $values = $settings->core_spam;
      unset($values['ipbans']);
      unset($values['emailbans']);
      unset($values['censor']);
      $form->populate($values);
    }
  }

  public function performanceAction()
  {
    $setting_file = APPLICATION_PATH . '/application/settings/cache.php';
    $default_file_path = APPLICATION_PATH . '/temporary/cache';

    if (file_exists($setting_file)) {
      $current_cache = include $setting_file;
    } else {
      $current_cache = array(
        'default_backend' => 'File',
        'frontend' => array (
          'core' => array (
            'automatic_serialization' => true,
            'cache_id_prefix' => 'Engine4_',
            'lifetime' => '300',
            'caching' => true,
          ),
        ),
        'backend' => array(
          'File' => array(
            'cache_dir' => APPLICATION_PATH . '/temporary/cache',
          ),
        ),
      );
    }
    $current_cache['default_file_path'] = $default_file_path;
    $this->view->form = $form = new Core_Form_Admin_Settings_Performance();

    // pre-fill form with proper cache type
    $form->populate($current_cache);

    // disable caching types not supported
    $disabled_type_options = $removed_type_options = array();
    foreach( $form->getElement('type')->options as $i => $backend ) {
      if( 'Apc' == $backend && !extension_loaded('apc') )
          $disabled_type_options[] = $backend;
      if( 'Memcached' == $backend && !extension_loaded('memcache') )
          $disabled_type_options[] = $backend;
      if( 'Xcache' == $backend && !extension_loaded('xcache') )
          $disabled_type_options[] = $backend;
    }
    $form->getElement('type')->setAttrib('disable', $disabled_type_options);

    // set required elements before checking for validity
    switch( $this->getRequest()->getPost('type') ) {
      case 'File':
        $form->getElement('file_path')->setRequired(true)->setAllowEmpty(false);
        break;
      case 'Memcached':
        $form->getElement('memcache_host')->setRequired(true)->setAllowEmpty(false);
        $form->getElement('memcache_port')->setRequired(true)->setAllowEmpty(false);
        break;
      case 'Xcache':
        $form->getElement('xcache_username')->setRequired(true)->setAllowEmpty(false);
        $form->getElement('xcache_password')->setRequired(true)->setAllowEmpty(false);
        break;
    }

    if (is_writable($setting_file) || (is_writable(dirname($setting_file)) && !file_exists($setting_file))) {
      // do nothing
    } else {
      //if( (is_file($setting_file) && !is_writable($setting_file))
      //    || (!is_file($setting_file) && is_dir(dirname($setting_file)) && !is_writable(dirname($setting_file))) ) {
      $phrase = Zend_Registry::get('Zend_Translate')->_('Changes made to this form will not be saved.  Please adjust the permissions (CHMOD) of file %s to 777 and try again.');
      $form->addError(sprintf($phrase, '/application/settings/cache.php'));
      return;
    }

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $this->view->isPost = true;
      $code = "<?php\ndefined('_ENGINE') or die('Access Denied');\nreturn ";

      $do_flush = false;
      foreach( $form->getElement('type')->options as $type => $label )
        if( array_key_exists($type, $current_cache['backend']) && $type != $this->_getParam('type') )
            $do_flush = true;

      $options = array();
      switch( $this->getRequest()->getPost('type') ) {
        case 'File':
          $options['file_locking'] = (bool) $this->_getParam('file_locking');
          $options['cache_dir'] = $this->_getParam('file_path');
          if( !is_writable($options['cache_dir']) ) {
            $options['cache_dir'] = $default_file_path;
            $form->getElement('file_path')->setValue($default_file_path);
          }
          break;
        case 'Memcached':
          $options['servers'][] = array(
            'host' => $this->_getParam('memcache_host'),
            'port' => (int) $this->_getParam('memcache_port'),
          );
          $options['compression'] = (bool) $this->_getParam('memcache_compression');
      }
      $current_cache['backend'] = array($this->_getParam('type') => $options);
      $current_cache['frontend']['core']['lifetime'] = $this->_getParam('lifetime');
      $current_cache['frontend']['core']['caching'] = (bool) $this->_getParam('enable');

      $code .= var_export($current_cache, true);
      $code .= '; ?>';

      // test write+read before saving to file
      $backend = null;
      if( !$current_cache['frontend']['core']['caching'] ) {
        $this->view->success = true;
      } else {
        $backend = Zend_Cache::_makeBackend($this->_getParam('type'), $options);
        if( $current_cache['frontend']['core']['caching'] && @$backend->save('test_value', 'test_id') && @$backend->test('test_id') ) {
          #$backend->remove('test_id');
          $this->view->success = true;
        } else {
          $this->view->success = false;
          $form->getElement('type')->setErrors(array('Unable to use this backend.  Please check your settings or try another one.'));
        }
      }

      // write settings to file
      if( $this->view->success && file_put_contents($setting_file, $code) ) {
        $form->addNotice('Your changes have been saved.');
      } elseif( $this->view->success ) {
        $form->addError('Your settings were unable to be saved to the
          cache file.  Please log in through FTP and either CHMOD 777 the file
          <em>/application/settings/cache.php</em>, or edit that file and
          replace the existing code with the following:<br/>
          <code>' . htmlspecialchars($code) . '</code>');
      }

      if( $backend instanceof Zend_Cache_Backend && ($do_flush || $form->getElement('flush')->getValue()) ) {
        $backend->clean();
        $form->getElement('flush')->setValue(0);
        $form->addNotice('Cache has been flushed.');
      }
    }
  }

  public function passwordAction()
  {
    // Super admins only?
    $viewer = Engine_Api::_()->user()->getViewer();
    $level = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
    if( !$viewer || !$level || $level->flag != 'superadmin' ) {
      return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }

    $this->view->form = $form = new Core_Form_Admin_Settings_Password();

    if( !$this->getRequest()->isPost() ) {
      $form->populate(array(
        'mode' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.admin.mode', 'none'),
        'timeout' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.admin.timeout'),
      ));
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();
    $values['reauthenticate'] = ( $values['mode'] == 'none' ? '0' : '1' );

    // If auth method is global and password is empty (in db), require them to enter one
    if( $values['mode'] == 'global' ) {
      if( !Engine_Api::_()->getApi('settings', 'core')->core_admin_password && empty($values['password']) ) {
        $form->addError('Please choose a password.');
        return;
      }
    }

    // Verify password
    if( !empty($values['password']) ) {
      if( $values['password'] != $values['password_confirm'] ) {
        $form->addError('Passwords did not match.');
        return;
      }
      if( strlen($values['password']) < 4 ) {
        $form->addError('Password must be at least four (4) characters.');
        return;
      }
      // Hash password
      $values['password'] = md5(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt') . $values['password']);
      unset($values['password_confirm']);

      $form->addNotice('Password updated.');
    } else {
      unset($values['password']);
      unset($values['password_confirm']);
    }

    Engine_Api::_()->getApi('settings', 'core')->core_admin = $values;

    $form->addNotice('Your changes have been saved.');
  }

  protected function _createRandomPassword($length = 6)
  {
    $chars = "abcdefghijkmnpqrstuvwxyz23456789";
    $charsLen = strlen($chars);
    $pass = '';
    for( $i = 0; $i < $length; $i++ ) {
      $pass .= substr($chars, mt_rand(0, $charsLen - 1), 1);
    }
    return $pass;
  }
}
