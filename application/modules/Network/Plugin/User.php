<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: User.php 8014 2010-12-09 21:41:27Z jung $
 * @author     Sami
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_Plugin_User extends Core_Model_Abstract
{
  public function onUserCreateAfter($event)
  {
    $payload = $event->getPayload();
    Engine_Api::_()->getDbtable('networks', 'network')->recalculate($payload);
  }

  public function onFieldsValuesSave($event)
  {
    $payload = $event->getPayload();
    if( $payload['item'] instanceof User_Model_User ) {
      Engine_Api::_()->getDbtable('networks', 'network')->recalculate($payload['item'], $payload['values']);
    }
  }

  public function onUserDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
      $networkApi = Engine_Api::_()->getDbtable('membership', 'network');
      $ids = $networkApi->getMembershipsOfIds($payload, true);
      if(!empty($ids)){
        foreach( $networkApi->getMembershipsOf($payload) as $network ) {
          $networkApi->removeMember($network, $payload);
        }
      }
    }
  }
}