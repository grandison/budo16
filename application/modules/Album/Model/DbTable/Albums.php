<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Albums.php 7395 2010-09-15 23:37:11Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Model_DbTable_Albums extends Engine_Db_Table
{
  protected $_rowClass = 'Album_Model_Album';

  public function getSpecialAlbum(User_Model_User $user, $type)
  {
    if( !in_array($type, array('wall', 'profile', 'message')) ) {
      throw new Album_Model_Exception('Unknown special album type');
    }

    $select = $this->select()
        ->where('owner_type = ?', $user->getType())
        ->where('owner_id = ?', $user->getIdentity())
        ->where('type = ?', $type)
        ->order('album_id ASC')
        ->limit(1);
    
    $album = $this->fetchRow($select);

    // Create wall photos album if it doesn't exist yet
    if( null === $album )
    {
      $translate = Zend_Registry::get('Zend_Translate');

      $album = $this->createRow();
      $album->owner_type = 'user';
      $album->owner_id = $user->getIdentity();
      $album->title = $translate->_(ucfirst($type) . ' Photos');
      $album->type = $type;

      if( $type == 'message' ) {
        $album->search = 0;
      } else {
        $album->search = 1;
      }

      $album->save();
      
      // Authorizations
      if( $type != 'message' ) {
        $auth = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($album, 'everyone', 'view',    true);
        $auth->setAllowed($album, 'everyone', 'comment', true);
      }
    }

    return $album;
  }
}