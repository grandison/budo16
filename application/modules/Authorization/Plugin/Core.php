<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Authorization_Plugin_Core
{
  public function onItemDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof Core_Model_Item_Abstract )
    {
      $table = Engine_Api::_()->getDbtable('allow', 'authorization');
      $table->delete(array(
        'resource_type = ?' => $payload->getType(),
        'resource_id = ?' => $payload->getIdentity(),
      ));
    }
  }
}