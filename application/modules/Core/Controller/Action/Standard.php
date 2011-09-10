<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Standard.php 8233 2011-01-17 22:38:28Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
abstract class Core_Controller_Action_Standard extends Engine_Controller_Action
{
  public $autoContext = true;
  
  public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
  {
        // Pre-init setSubject
        try {
          if( '' !== ($subject = trim((string) $request->getParam('subject'))) ) {
            $subject = Engine_Api::_()->getItemByGuid($subject);
            if( ($subject instanceof Core_Model_Item_Abstract) && $subject->getIdentity() && !Engine_Api::_()->core()->hasSubject() ) {
              Engine_Api::_()->core()->setSubject($subject);
            }
          }
        } catch( Exception $e ) {
          // Silence
          //throw $e;
        }

        // Parent
        parent::__construct($request, $response, $invokeArgs);
  }
  
  public function postDispatch()
  {
    $layoutHelper = $this->_helper->layout;
    if( $layoutHelper->isEnabled() && !$layoutHelper->getLayout() )
    {
      $layoutHelper->setLayout('default');
    }
    if( 'default' == $layoutHelper->getLayout() && $this->_getParam('module', false) )
    {
      // Increment page views and referrer
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.views');
      Engine_Api::_()->getDbtable('referrers', 'core')->increment();
    }
  }

  protected function _redirectCustom($to, $options = array())
  {
    $options = array_merge(array(
      'prependBase' => false
    ), $options);
    
    // Route
    if( is_array($to) && empty($to['uri']) ) {
      $route = ( !empty($to['route']) ? $to['route'] : 'default' );
      $reset = ( isset($to['reset']) ? $to['reset'] : true );
      unset($to['route']);
      unset($to['reset']);
      $to = $this->_helper->url->url($to, $route, $reset);
    // Uri with options
    } else if( is_array($to) && !empty($to['uri']) ) {
      $to = $to['uri'];
      unset($params['uri']);
      $params = array_merge($params, $to);
    } else if( is_object($to) && method_exists($to, 'getHref') ) {
      $to = $to->getHref();
    }

    if( !is_scalar($to) ) {
      $to = (string) $to;
    }

    $message = ( !empty($options['message']) ? $options['message'] : 'Changes saved!' );

    switch( $this->_helper->contextSwitch->getCurrentContext() ) {
      case 'smoothbox':
        return $this->_forward('success', 'utility', 'core', array(
          'messages' => array($message),
          'smoothboxClose' => true,
          'redirect' => $to
        ));
        break;
      case 'json': case 'xml': case 'async':
        // What should be do here?
        //break;
      default:
        return $this->_helper->redirector->gotoUrl($to, $options);
        break;
    }
  }
}