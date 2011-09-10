<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Widget.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Content_Element_Widget extends Engine_Content_Element_Abstract
{
  protected $_action;

  protected $_request;

  protected $_throwExceptions = false;
  
  protected $_widget;

  public function __construct($options = null)
  {
    parent::__construct($options);
    //$this->_throwExceptions = ( APPLICATION_ENV === 'development' );
  }

  public function setAction($action)
  {
    $this->_action = $action;
    return $this;
  }

  public function setRequest(Zend_Controller_Request_Abstract $request)
  {
    $this->_request = $request;
    return $this;
  }

  public function setThrowExceptions($flag = true)
  {
    $this->_throwExceptions = (bool) $flag;
    return $this;
  }

  public function getWidget()
  {
    return $this->_widget;
  }
  
  protected function _render()
  {
    try {
      $contentInstance = Engine_Content::getInstance();
      $this->_widget = $contentInstance->loadWidget($this->getName());
      $this->_widget->setElement($this);
      if( null !== $this->_request ) {
        $this->_widget->setRequest($this->_request);
      }
      $this->_widget->render($this->_action);
      return $this->_widget->getContent();
    } catch( Exception $e ) {
      $this->setNoRender();
      if( $this->_throwExceptions ) {
        throw $e;
      } else {
        if( !($e instanceof Engine_Exception) ) {
          $log = Zend_Registry::get('Zend_Log');
          $log->log($e->__toString(), Zend_Log::CRIT);
        }
        // Silence
        //if( APPLICATION_ENV === 'development' ) {
        //  trigger_error('Exception thrown while rendering widget: ' . $e->__toString(), E_USER_WARNING);
        //}
      }
      return '';
    }
  }
}