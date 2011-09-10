<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ContextSwitch.php 8793 2011-04-05 23:45:10Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Controller_Action_Helper_ContextSwitch extends
  Zend_Controller_Action_Helper_ContextSwitch
{
  protected $_autoContextSwitchKey = 'autoContext';

  protected $_autoSwitchLayout = true;

  public function __construct($options = null)
  {
    if ($options instanceof Zend_Config) {
        $this->setConfig($options);
    } elseif (is_array($options)) {
        $this->setOptions($options);
    }

    if (empty($this->_contexts)) {
        $this->addContexts(array(
            'json' => array(
                'suffix'    => 'json',
                'headers'   => array('Content-Type' => 'application/json'),
                'callbacks' => array(
                    'init' => 'initJsonContext',
                    'post' => 'postJsonContext'
                )
            ),
            'xml'  => array(
                'suffix'    => 'xml',
                'headers'   => array('Content-Type' => 'application/xml'),
            ),
            'html' => array(
                'suffix'    => '',
                'headers'   => array('Content-Type' => 'text/html'),
                /*
                'callbacks' => array(
                    'init' => 'initHtmlContext',
                    'post' => 'postHtmlContext'
                )*/
            ),
            'async' => array(
                'suffix'    => '',
                'headers'   => array('Content-Type' => 'text/html'),
                'layout' => 'async',
            ),
            'smoothbox' => array(
                'suffix'    => '',
                'headers'   => array('Content-Type' => 'text/html'),
                'layout' => 'default-simple',
            ),
            'frame' => array(
                'suffix'    => '',
                'headers'   => array('Content-Type' => 'text/html'),
                'layout' => 'default-simple',
            )
        ));
    }

    $this->init();
  }
  
  public function preDispatch()
  {
    $controller = $this->getActionController();
    if( !empty($controller->{$this->_autoContextSwitchKey}) )
    {
      $actionName = $this->getActionController()->getRequest()->getActionName();
      $this
        ->addActionContext($actionName, 'json')
        ->addActionContext($actionName, 'html')
        ->addActionContext($actionName, 'async')
        ->addActionContext($actionName, 'smoothbox')
        ->addActionContext($actionName, 'frame')
        ->initContext();
    }
  }

  public function addContext($context, array $spec)
  {
    parent::addContext($context, $spec);

    $this->setLayout($context, (isset($spec['layout']) ? $spec['layout'] : ''));

    return $this;
  }
  
  public function initContext($format = null)
  {
    parent::initContext($format);

    // Hack to prevent duplicate content-type headers
    $pos = false;
    $headers = $this->getResponse()->getHeaders();
    foreach( $headers as $key => $value ) {
      if( strtolower($key) == 'content-type' ) {
        if( !$pos ) {
          $pos = $key;
        } else {
          unset($headers[$pos]);
        }
      }
    }

    if( null === $this->_currentContext || !$this->getAutoSwitchLayout() )
    {
      return;
    }

    $layoutName = $this->getLayout($this->_currentContext);
    if( $layoutName )
    {
      $layout = Zend_Layout::getMvcInstance();
      $layout->enableLayout()
        ->setLayout($layoutName);
    }
  }

  public function initHtmlContext()
  {
  }

  public function postHtmlContext()
  {
  }



  public function setLayout($context, $layout)
  {
    if (!isset($this->_contexts[$context])) {
      /**
       * @see Zend_Controller_Action_Exception
       */
      require_once 'Zend/Controller/Action/Exception.php';
      throw new Zend_Controller_Action_Exception(sprintf('Cannot set suffix; invalid context type "%s"', $context));
    }

    $this->_contexts[$context]['layout'] = $layout;
  }
  
  public function getLayout($type)
  {
    if (!isset($this->_contexts[$type])) {
      /**
       * @see Zend_Controller_Action_Exception
       */
      require_once 'Zend/Controller/Action/Exception.php';
      throw new Zend_Controller_Action_Exception(sprintf('Cannot retrieve suffix; invalid context type "%s"', $type));
    }

    return $this->_contexts[$type]['layout'];
  }
  
  public function getAutoSwitchLayout()
  {
    return (bool) $this->_autoSwitchLayout;
  }

  public function setAutoSwitchLayout($flag = false)
  {
    $this->_autoSwitchLayout = (bool) $flag;
    return $this;
  }
}