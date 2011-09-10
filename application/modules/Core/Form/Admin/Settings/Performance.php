<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Performance.php 7974 2010-12-07 22:58:59Z char $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Settings_Performance extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this->setTitle('Performance & Caching');
    $this->setDescription(strtoupper(get_class($this) . '_description'));

    // disable form if not in production mode
    $attribs = array();
    if (APPLICATION_ENV != 'production') {
      $attribs = array('disabled' => 'disabled', 'readonly' => 'readonly');
      $this->addError('Note: Caching is disabled when your site is in development mode. Your site must be in production mode to modify the settings below.');
    }

    $this->addElement('Radio', 'enable', array(
      'label' => 'Use Cache?',
      'description' => strtoupper(get_class($this) . '_enable_description'),
      'required' => true,
      'multiOptions' => array(
        1 => 'Yes, enable caching.',
        0 => 'No, do not enable caching.',
      ),
      'attribs' => $attribs,
    ));

    $this->addElement('Text', 'lifetime', array(
      'label' => 'Cache Lifetime',
      'description' => strtoupper(get_class($this) . '_lifetime_description'),
      'size' => 5,
      'maxlength' => 4,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('Int'),
      ),
      'attribs' => $attribs,
    ));

    $this->addElement('Radio', 'type', array(
      'label' => 'Caching Feature',
      'description' => strtoupper(get_class($this) . '_type_description'),
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        'File'      => 'File-based',
        'Memcached' => 'Memcache',
        'Apc'       => 'APC',
        'Xcache'    => 'Xcache',
      ),//Zend_Cache::$standardBackends,
      'onclick' => 'updateFields();',
      'attribs' => $attribs,
    ));

    $this->addElement('Text', 'file_path', array(
      'label' => 'File-based Cache Directory',
      'description' => strtoupper(get_class($this) . '_file_path_description'),
      'attribs' => $attribs,
    ));

    $this->addElement('Checkbox', 'file_locking', array(
      'label' => 'File locking?',
      'attribs' => $attribs,
    ));

    $this->addElement('Text', 'memcache_host', array(
      'label' => 'Memcache Host',
      'description' => 'Can be a domain name, hostname, or an IP address (recommended)',
      'attribs' => $attribs,
    ));

    $this->addElement('Text', 'memcache_port', array(
      'label' => 'Memcache Port',
      'attribs' => $attribs,
    ));

    $this->addElement('Checkbox', 'memcache_compression', array(
      'label' => 'Memcache compression?',
      'title' => 'Title?',
      'description' => 'Compression will decrease the amount of memory used, however will increase processor usage.',
      'attribs' => $attribs,
    ));


    $this->addElement('Text', 'xcache_username', array(
      'label' => 'Xcache Username',
      'attribs' => $attribs,
    ));

    $this->addElement('Text', 'xcache_password', array(
      'label' => 'Xcache Password',
      'attribs' => $attribs,
    ));

    $this->addElement('Checkbox', 'flush', array(
      'label' => 'Flush cache?',
      'attribs' => $attribs,
    ));
    
    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'attribs' => $attribs,
    ));

  }

  public function populate($current_cache=array()) {

    $enabled = true;
    if (isset($current_cache['frontend']['core']['caching']))
      $enabled = $current_cache['frontend']['core']['caching'];
    $this->getElement('enable')->setValue($enabled);

    $backend = Engine_Cache::getDefaultBackend();
    if (isset($current_cache['backend'])) {
      $backend = array_keys($current_cache['backend']);
      $backend = $backend[0];
    }
    $this->getElement('type')->setValue($backend);

    $file_path = $current_cache['default_file_path'];
    if (isset($current_cache['backend']['File']['cache_dir']))
      $file_path = $current_cache['backend']['File']['cache_dir'];
    $this->getElement('file_path')->setValue( $file_path );

    $file_locking = 1;
    if (isset($current_cache['backend']['File']['file_locking']))
      $file_locking = $current_cache['backend']['File']['file_locking'];
    $this->getElement('file_locking')->setValue( $file_locking );

    if( isset($current_cache['frontend']['core']['lifetime']) ){
      $lifetime = $current_cache['frontend']['core']['lifetime'];
    } else {
      $lifetime = 300; // 5 minutes
    }
    if (isset($current_cache['frontend']['core']['options']['lifetime']))
      $lifetime = $current_cache['frontend']['core']['options']['lifetime'];
    $this->getElement('lifetime')->setValue($lifetime);

    $memcache_host = '127.0.0.1';
    $memcache_port = '11211';
    if (isset($current_cache['backend']['Memcache']['servers'][0]['host']))
      $memcache_host = $current_cache['backend']['Memcache']['servers'][0]['host'];
    if (isset($current_cache['backend']['Memcache']['servers'][0]['port']))
      $memcache_port = $current_cache['backend']['Memcache']['servers'][0]['port'];
    $this->getElement('memcache_host')->setValue($memcache_host);
    $this->getElement('memcache_port')->setValue($memcache_port);


  }
}