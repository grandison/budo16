<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Album.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Group_Model_Album extends Core_Model_Item_Collection
{
  protected $_parent_type = 'group';

  protected $_owner_type = 'group';

  protected $_children_types = array('group_photo');

  protected $_collectible_type = 'group_photo';

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'group_profile',
      'reset' => true,
      'id' => $this->getGroup()->getIdentity(),
      //'album_id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getGroup()
  {
    return $this->getOwner();
    //return Engine_Api::_()->getItem('group', $this->group_id);
  }

  public function getAuthorizationItem()
  {
    return $this->getParent('group');
  }

  protected function _delete()
  {
    // Delete all child posts
    $photoTable = Engine_Api::_()->getItemTable('group_photo');
    $photoSelect = $photoTable->select()->where('album_id = ?', $this->getIdentity());
    foreach( $photoTable->fetchAll($photoSelect) as $groupPhoto ) {
      $groupPhoto->delete();
    }

    parent::_delete();
  }
}