<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Observer
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Callback.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Observer/Interface.php';

/**
 * @category   Engine
 * @package    Engine_Observer
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Observer_Callback implements Engine_Observer_Interface
{
  protected $_callback;
  
  public function __construct($callback, $method = null)
  {
    if( null !== $method ) {
      $callback = array($callback, $method);
    }
    
    if( !is_callable($callback) ) {
      throw new Engine_Observer_Exception(sprintf('Specified callback is not callable.'));
    }
    
    $this->_callback = $callback;
  }

  public function notify($event)
  {
    call_user_func($this->_callback, $event);
  }
}