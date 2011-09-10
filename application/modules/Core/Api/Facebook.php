<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Facebook.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Api_Facebook extends Core_Api_Abstract
{
  protected $_api;

  protected $_key;

  protected $_secret;

  public function getSecret()
  {
    if( null === $this->_secret ) {
      $this->_initialize();
    }

    return $this->_secret;
  }

  public function getKey()
  {
    if( null === $this->_key ) {
      $this->_initialize();
    }

    return $this->_key;
  }
  
  public function getApi()
  {
    if( null === $this->_api ) {
      $this->_initialize();
    }
    
    return $this->_api;
  }

  protected function _initialize()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    if( empty($settings['key']) || empty($settings['secret']) ) {
      $this->_key = false;
      $this->_secret = false;
      $this->_api = false;
    } else {
      $this->_key = $settings['key'];
      $this->_secret = $settings['secret'];
      $this->_api = new Facebook_Core($this->_key, $this->_secret);
    }
  }
}