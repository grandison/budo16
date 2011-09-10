<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Message.php 7418 2010-09-20 00:18:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Messages_Model_Message extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'messages_general',
      'reset' => true,
      'action' => 'view',
      'id' => $this->conversation_id,
      'message_id' => $this->getIdentity()
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getAttachment()
  {
    if( !empty($this->attachment_type) && !empty($this->attachment_id) )
    {
      return Engine_Api::_()->getItem($this->attachment_type, $this->attachment_id);
    }
  }
}