<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Composer.php 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Plugin_Composer extends Core_Plugin_Abstract
{
  public function onAttachPhoto($data)
  {
    if( !is_array($data) || empty($data['photo_id']) ) {
      return;
    }

    $photo = Engine_Api::_()->getItem('album_photo', $data['photo_id']);

    // make the image public

    // CREATE AUTH STUFF HERE
    /*
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'everyone');
    foreach( $roles as $i=>$role )
    {
      $auth->setAllowed($photo, $role, 'view', ($i <= $roles));
      $auth->setAllowed($photo, $role, 'comment', ($i <= $roles));
    }*/

    if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() )
    {
      return;
    }

    return $photo;
  }
}