<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: File.php 8438 2011-02-10 20:41:56Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Storage_Model_File extends Core_Model_Item_Abstract
{
  // Item stuff

  public function getPhotoUrl($type = null)
  {
    if( !$type ) {
      if( empty($this->type) ) {
        $file = $this;
      } else if( !empty($this->parent_file_id) ) {
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->parent_file_id);
      } else {
        $file = $this;
      }
    } else {
      if( !empty($this->type) ) {
        if( $this->type == $type ) {
          $file = $this;
        } else if( !empty($this->parent_file_id) ) {
          $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->parent_file_id, $type);
        } else {
          $file = $this;
        }
      } else {
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
      }
    }

    // no file
    if( !$file ) {
      return null;
    }

    // should we filter out non-image extensions?
    if( !in_array($file->extension, array('jpg', 'png', 'jpeg', 'gif', 'tif', 'bmp')) ) {
      return null;
    }

    return $file->map();
  }

  public function getHref()
  {
    return $this->getStorageService()->map($this);
  }

  public function getParent($recurseType = null)
  {
    if( $this->parent_type == 'temporary' ||
        $this->parent_type == 'system' ) {
      return null;
    } else {
      return parent::getParent($recurseType);
    }
  }

  // Storage stuff

  public function getStorageService($type = null)
  {
    $type = $type ? $type : $this->service_id;
    return Engine_Api::_()->getDbtable('services', 'storage')
        ->getService($type);
  }

  public function getChildren()
  {
    $table = $this->getTable();
    $select = $table->select()
      ->where('parent_file_id = ?', $this->file_id);
    return $table->fetchAll($select);
  }



  // Simple operations
  
  public function bridge(Storage_Model_File $file, $type, $isChild = false)
  {
    $child  = ( $isChild ? $this : $file );
    $parent = ( $isChild ? $file : $this );
    $child->parent_file_id = $parent->file_id;
    $child->type = $type;
    $child->save();

    return $this;
  }

  public function map()
  {
    $uri = $this->getStorageService()->map($this);
    $uri .= '?c=' . substr($this->hash, 0, 4);
    return $uri;
  }

  public function store($spec)
  {
    $service = $this->getStorageService();
    $isCreate = empty($this->file_id);

    $meta = $this->getStorageService()->fileInfo($spec);

    $this->setFromArray($meta);
    $this->service_id = $service->getIdentity();
    if( empty($this->user_id) &&
        $this->parent_type != 'temporary' &&
        $this->parent_type != 'system' ) {
      $this->user_id = Engine_Api::_()->user()->getViewer()->getIdentity(); // @todo this is wrong
    }

    // Have to initialize now if creation
    if( $isCreate ) {
      $this->save();   
    }
    
    // Store file to service
    $path = $service->store($this, $meta['tmp_name']);

    // If a file existed before and not same name, try to remove the old one
    if( !empty($this->storage_path) &&
        $this->storage_path != 'temp' &&
        $this->storage_path != $path ) {
      $service->removeFile($this->storage_path);
    }

    // We still have to update the path even if we just created it
    $this->storage_path = $path;
    $this->save();
    
    return $this;
  }
    
  public function write($data, $meta)
  {
    $service = $this->getStorageService();
    $isCreate = empty($this->file_id);

    $meta['hash'] = md5($data);
    $meta['size'] = strlen($data);

    $this->setFromArray($meta);
    $this->service_id = $service->getIdentity();
    if( empty($this->user_id) &&
        $this->parent_type != 'temporary' &&
        $this->parent_type != 'system' ) {
      $this->user_id = Engine_Api::_()->user()->getViewer()->getIdentity(); // @todo this is wrong
    }

    // Have to initialize now if creation
    if( $isCreate ) {
      $this->save();
    }
    
    // Write data to service
    $path = $service->write($this, $data);

    // If a file existed before and not same name, try to remove the old one
    if( !empty($this->storage_path) &&
        $this->storage_path != 'temp' &&
        $this->storage_path != $path ) {
      $service->removeFile($this->storage_path);
    }
    
    // We still have to update the path even if we just created it
    $this->storage_path = $path;
    $this->save();

    return $this;
  }

  public function read()
  {
    return $this->getStorageService()->read($this);
  }

  public function remove()
  {
    $this->getStorageService()->remove($this);
    $this->delete();
  }

  public function temporary()
  {
    return $this->getStorageService()->temporary($this);
  }



  // Complex

  public function move($storage)
  {
    $originalStorage = $this->getStorageService();
    
    if( !is_object($storage) ) {
      $storage = $this->getStorageService($storage);
    }

    if( !($storage instanceof Storage_Service_Interface) ) {
      throw new Exception("Storage must be an instance of File_Service_Storage_Interface");
    }

    if( $storage->getIdentity() == $originalStorage->getIdentity() ) {
      throw new Exception('You may not move a file within a storage type');
    }

    $originalPath = $this->storage_path;

    // Store using temp file
    $tmp_file = $originalStorage->temporary($this);
    $path = $storage->store($this, $tmp_file);

    $this->service_id = $storage->getIdentity();
    $this->storage_path = $path;
    $this->modified_date = date('Y-m-d H:i:s');
    $this->save();
    
    // Now remove original and temporary file
    $originalStorage->removeFile($originalPath);
    @unlink($tmp_file);

    return $this;
  }

  public function copy($params = array(), $storage = null)
  {
    $storage = $this->getStorageService($storage);

    if( !($storage instanceof Storage_Service_Interface) ) {
      throw new Exception("Storage must be an instance of File_Service_Storage_Interface");
    }

    // Create new row
    // @todo store this in main model?
    $params = array_merge($this->toArray(), $params);
    $params['service_id'] = $storage->getIdentity();
    $params['storage_path'] = 'temp';
    unset($params['file_id']);

    $newThis = $this->getTable()->createRow();
    $newThis->setFromArray($params);
    $newThis->save();

    // Read into temp file and store
    $tmp_file = $this->getStorageService()->temporary($this);
    $path = $storage->store($this, $tmp_file);
    
    // Update
    // @todo make sure file is removed if this fails
    $newThis->storage_path = $path;
    $newThis->save();

    // Remove temp file
    @unlink($tmp_file);

    return $newThis;
  }

  public function updatePath()
  {
    $service = $this->getStorageService();

    $oldPath = $this->storage_path;
    $newPath = $service->getScheme()->generate($this->toArray());

    // No update required
    if( $oldPath == $newPath ) {
      return $this;
    }

    // @todo maybe update this to move the file internally
    $tmpFile = $this->temporary();

    // Store file to service
    $path = $service->store($this, $tmpFile);

    // Update the path and remove the old file if necessary
    if( $oldPath != $path ) {
      $this->storage_path = $path;
      $this->save();

      $service->removeFile($oldPath);
    }

    return $this;
  }

  protected function _delete()
  {
    if( $this->_disableHooks ) return;
    
    try {
      $this->getStorageService()->remove($this);
    } catch( Exception $e ) {
      
    }
    //$this->remove();
  }
}