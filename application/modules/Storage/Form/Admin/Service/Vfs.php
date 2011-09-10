<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Vfs.php 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Storage_Form_Admin_Service_Vfs extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: adapter
    $this->addElement('Select', 'adapter', array(
      'label' => 'VFS Adapter',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        'ftp' => 'FTP',
        'ssh' => 'SSH/SCP',
      )
    ));

    // Element: host
    $this->addElement('Text', 'params_host', array(
      'label' => 'Remote Host',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Element: username
    $this->addElement('Text', 'params_username', array(
      'label' => 'Username',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Element: password
    $this->addElement('Text', 'params_password', array(
      'label' => 'Password',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Element: path
    $this->addElement('Text', 'params_path', array(
      'label' => 'Path',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Element: baseUrl
    $this->addElement('Text', 'baseUrl', array(
      'label' => 'Base URL',
      'required' => true,
      'allowEmpty' => false,
    ));

    parent::init();
  }

  public function isValid($data)
  {
    $valid = parent::isValid($data);

    // Custom valid
    if( $valid ) {
      $params = array();
      foreach( $data as $key => $value ) {
        if( false !== strpos($key, '_') ) {
          list($p, $c) = explode('_', $key, 2);
          if( $p == 'params' ) {
            $params[$c] = $value;
          }
        }
      }

      try {
        $vfs = Engine_Vfs::factory($data['adapter'], $params);
        $vfs->getSystemType(); // Used to test connection
      } catch( Exception $e ) {
        $this->addError('Could not create VFS connection. Error was:');
        $this->addError($e->getMessage());
        return false;
      }
    }

    return $valid;
  }
}