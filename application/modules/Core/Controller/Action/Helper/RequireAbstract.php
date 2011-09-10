<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RequireAbstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Controller_Action_Helper_RequireAbstract extends
  Zend_Controller_Action_Helper_Abstract
{
  protected $_require = false;

  protected $_actionRequires = array();

  protected $_noForward = false;

  protected $_errorAction = array('error', 'error', 'core');

  public function direct()
  {
    $this->setRequire(true);
    return $this;
  }

  public function isValid()
  {
    $valid = $this->checkRequire();

    if( !$valid && !$this->getNoForward() )
    {
      $this->forward();
    }

    return $valid;
  }

  public function forward()
  {
    // Stolen from Zend_Controller_Action::_forward
    list($action, $controller, $module) = $this->getErrorAction();
    $request = $this->getActionController()->getRequest();

    if (null !== $controller) {
      $request->setControllerName($controller);
    }

    if (null !== $module) {
      $request->setModuleName($module);
    }

    $request->setActionName($action)
      ->setDispatched(false);
  }
  
  public function preDispatch()
  {
    // Require all
    if( $this->getRequire() || $this->hasActionRequire($this->getActionController()->getRequest()->getActionName()) ) {
      $this->isValid();
      // Should we do a reset here?
      $this->reset();
      //$this->setRequire(false);
    }
  }

  public function postDispatch()
  {
    $this->reset();
  }

  public function setRequire($flag = true)
  {
    $this->_require = (bool) $flag;
    return $this;
  }

  public function getRequire()
  {
    return (bool) $this->_require;
  }

  public function setNoForward($flag = true)
  {
    $this->_noForward = (bool) $flag;
    return $this;
  }

  public function getNoForward()
  {
    return $this->_noForward;
  }

  public function setErrorAction($action, $controller = null, $module = null)
  {
    $this->_errorAction = array($action, $controller, $module);
    return $this;
  }

  public function getErrorAction()
  {
    if( is_null($this->_errorAction) )
    {
      throw new Zend_Controller_Action_Exception('No action was set');
    }
    return $this->_errorAction;
  }

  public function reset()
  {
    $this->_errorAction = array('error', 'error', 'core');
    $this->_noForward = false;
    $this->_require = false;
    $this->_actionRequires = array();

    return $this;
  }
  


  // Action requires

  public function addActionRequire($action, $options = true)
  {
    $this->_actionRequires[$action] = $options;
    return $this;
  }

  public function addActionRequires(array $actions)
  {
    foreach( $actions as $key => $value )
    {
      if( is_numeric($key) ) {
        $this->addActionRequire($value);
      } else {
        $this->addActionRequire($key, $value);
      }
    }

    return $this;
  }

  public function getActionRequire($action)
  {
    if( !$this->hasActionRequire($action) )
    {
      return null;
    }
    
    return $this->_actionRequires[$action];
  }

  public function hasActionRequire($action)
  {
    return isset($this->_actionRequires[$action]);
  }

  public function removeActionRequire($action)
  {
    unset($this->_actionRequired[$action]);
    return $this;
  }

  

  // Abstract

  abstract public function checkRequire();
}