<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Callback.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_Callback extends Engine_Sanity_Test_Abstract
{
  protected $_callback;

  protected $_options;

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value)
    {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      } else {
        $this->_options[$key] = $value;
      }
    }

    return $this;
  }
  
  public function setCallback($callback)
  {
    if( is_callable($callback) ) {
      $this->_callback = $callback;
    }
    return $this;
  }

  public function getCallback()
  {
    return $this->_callback;
  }

  public function error($code, $key)
  {
    $this->_error($code, $key);
    return $this;
  }

  public function execute()
  {
    $callback = $this->getCallback();

    if( !empty($callback) && is_callable($callback) ) {
      call_user_func($callback, $this, $this->_options);
    }
  }
}