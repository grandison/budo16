<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Announcement_Plugin_Core
{
  public function onItemDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof Core_Model_Item_Abstract && $payload->getType() === 'user' )
    {
      $table = Engine_Api::_()->getDbtable('announcements', 'announcement');
      foreach( $table->fetchAll($table->select()->where('user_id = ?', $payload->getIdentity())) as $announcement )
      {
        $announcement->delete();
      }
    }
  }
}