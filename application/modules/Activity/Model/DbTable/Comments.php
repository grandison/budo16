<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Comments.php 8478 2011-02-16 04:01:47Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Model_DbTable_Comments extends Core_Model_DbTable_Comments
{
  protected $_rowClass = 'Activity_Model_Comment';

  public function getResourceType()
  {
    return 'activity_action';
  }
}