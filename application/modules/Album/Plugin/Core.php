<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8301 2011-01-25 07:54:45Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Album_Plugin_Core
{
  public function onStatistics($event)
  {
    $table  = Engine_Api::_()->getDbTable('photos', 'album');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'photo');
  }

  public function onUserProfilePhotoUpload($event)
  {
    $payload = $event->getPayload();
    if( empty($payload['user']) || !($payload['user'] instanceof Core_Model_Item_Abstract) )
    {
      return;
    }
    if( empty($payload['file']) || !($payload['file'] instanceof Storage_Model_File) )
    {
      return;
    }

    $viewer = $payload['user'];
    $file = $payload['file'];

    // Get album
    $table = Engine_Api::_()->getDbtable('albums', 'album');
    $album = $table->getSpecialAlbum($viewer, 'profile');

    $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
    $photo = $photoTable->createRow();
    $photo->setFromArray(array(
      'owner_type' => 'user',
      'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
    ));
    $photo->save();
    $photo->setPhoto($file);
    
    $photo->collection_id = $album->album_id;
    $photo->save();

    if( !$album->photo_id )
    {
      $album->photo_id = $photo->getIdentity();
      $album->save();
    }

    $auth      = Engine_Api::_()->authorization()->context;
    $auth->setAllowed($photo, 'everyone', 'view',    true);
    $auth->setAllowed($photo, 'everyone', 'comment', true);
    $auth->setAllowed($album, 'everyone', 'view',    true);
    $auth->setAllowed($album, 'everyone', 'comment', true);
    
    $event->addResponse($photo);
  }

  public function onUserDeleteAfter($event)
  {
    $payload = $event->getPayload();
    $user_id = $payload['identity'];
    $table   = Engine_Api::_()->getDbTable('albums', 'album');
    $select = $table->select()->where('owner_id = ?', $user_id);
    $select = $select->where('owner_type = ?', 'user');
    $rows = $table->fetchAll($select);
    foreach ($rows as $row)
    {
      $row->delete();
    }
    $table   = Engine_Api::_()->getDbTable('photos', 'album');
    $select = $table->select()->where('owner_id = ?', $user_id);
    $select = $select->where('owner_type = ?', 'user');
    $rows = $table->fetchAll($select);
    foreach ($rows as $row)
    {
      $row->delete();
    }
  }
}
