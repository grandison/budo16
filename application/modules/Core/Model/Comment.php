<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Comment.php 8478 2011-02-16 04:01:47Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_Comment extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getHref()
  {
    // @todo take directly to the comment
    if( isset($this->resource_type) ) {
      return Engine_Api::_()->getItem($this->resource_type, $this->resource_id)->getHref()
        . '#comment-' . $this->comment_id;
    } else if( method_exists($this->getTable(), 'getResourceType') ) {
      $tmp = Engine_Api::_()->getItem($this->getTable()->getResourceType(), $this->resource_id);
      return $tmp->getHref() . '#comment-' . $this->comment_id;
    } else {
      return parent::getHref(); // @todo fix this
    }
  }

  public function getOwner($type = null)
  {
    $poster = $this->getPoster();
    if( null === $type && $type !== $poster->getType() ) {
      return $poster->getOwner($type);
    }
    return $poster;
  }

  public function getPoster()
  {
    return Engine_Api::_()->getItem($this->poster_type, $this->poster_id);
  }

  public function getAuthorizationItem()
  {
    if( isset($this->resource_type) ) {
      return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
    } else if( method_exists($this->getTable(), 'getResourceType') ) {
      $tmp = Engine_Api::_()->getItem($this->getTable()->getResourceType(), $this->resource_id);
      return $tmp->getAuthorizationItem(); // Sigh
    } else {
      return $this;
    }
  }

  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }
}