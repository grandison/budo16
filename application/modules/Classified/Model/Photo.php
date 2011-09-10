<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Photo.php 8215 2011-01-14 07:40:36Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Model_Photo extends Core_Model_Item_Collectible
{
  protected $_parent_type = 'classified_album';

  protected $_owner_type = 'user';

  protected $_collection_type = 'classified_album';

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'classified_extended',
      'reset' => true,
      'controller' => 'photo',
      'action' => 'view',
      'classified_id' => $this->getCollection()->getOwner()->getIdentity(),
      //'album_id' => $this->collection_id,
      'photo_id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getPhotoUrl($type = null)
  {
    if( empty($this->file_id) ) {
      return null;
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
    if( !$file ) {
      return null;
    }

    return $file->map();
  }

  public function getClassified()
  {
    return Engine_Api::_()->getItem('classified', $this->classified_id);
    //return $this->getCollection()->getGroup();
  }

  public function isSearchable()
  {
    $collection = $this->getCollection();
    if( !$collection instanceof Core_Model_Item_Abstract )
    {
      return false;
    }
    return $collection->isSearchable();
  }

  public function getAuthorizationItem()
  {
    return $this->getParent('classified');
  }


  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   **/
  public function comments()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   **/
  public function tags()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  protected function _postDelete()
  {
    if( $this->_disableHooks ) return;

    // This is dangerous, what if something throws an exception in postDelete
    // after the files are deleted?
    try
    {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
      $file->remove();
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
      $file->remove();

      $album = $this->getCollection();

      if( (int) $album->photo_id == (int) $this->getIdentity() )
      {
	$album->photo_id = $this->getNextCollectible()->getIdentity();
	$album->save();
      }
    }
    catch( Exception $e )
    {
      // @todo completely silencing them probably isn't good enough
      //throw $e;
    }
  }
}