<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Posts.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Model_DbTable_Posts extends Engine_Db_Table
{
  protected $_rowClass = 'Forum_Model_Post';

  public function getChildrenSelectOfForumTopic($topic)
  {
    $select = $this->select()->where('topic_id = ?', $topic->topic_id);
    return $select;
  }

}