<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Status.php 7549 2010-10-05 01:02:44Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Status extends Engine_Db_Table
{
  //protected $_rowClass = 'Core_Model_Status';
  
  public function setStatus(Core_Model_Item_Abstract $resource, $body)
  {
    // Create status row
    $row = $this->createRow();

    if( isset($row->resource_type) ) {
      $row->resource_type = $resource->getType();
    }

    $row->resource_id = $resource->getIdentity();
    $row->body = $body;
    $row->creation_date = date('Y-m-d H:i:s');
    $row->save();

    // Update resource if necessary
    $resourceModified = false;

    if( isset($resource->status) ) {
      $resourceModified = true;
      $resource->status = $body;
    }

    if( isset($resource->status_date) ) {
      $resourceModified = true;
      $resource->status_date = date('Y-m-d H:i:s');
    }

    if( isset($resource->status_count) ) {
      $resourceModified = true;
      $resource->status_count = new Zend_Db_Expr('status_count + 1');
    }

    if( $resourceModified ) {
      $resource->save();
    }

    return $row;
  }

  public function getStatus(Core_Model_Item_Abstract $resource, $status_id)
  {
    $select = $this->select();

    if( in_array('resource_type', $this->info('cols')) ) {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select
      ->where('resource_id = ?', $resource->getIdentity())
      ->where('status_id = ?', (int) $status_id)
      ->limit(1);

    return $this->fetchRow($select);
  }

  public function clearStatus(Core_Model_Item_Abstract $resource)
  {
    // Update resource if necessary
    $resourceModified = false;

    if( isset($resource->status) ) {
      $resourceModified = true;
      $resource->status = '';
    }

    if( isset($resource->status_date) ) {
      $resourceModified = true;
      $resource->status_date = '00-00-0000';
    }

    if( $resourceModified ) {
      $resource->save();
    }

    return $this;
  }

  public function deleteStatus(Core_Model_Item_Abstract $resource, $status_id)
  {
    $row = $this->getStatus($resource, $status_id);

    if( !$row ) {
      return $this;
    }
    
    // Delete
    $row->delete();

    // Get previous?
    $previous = $this->getLastStatus($resource);

    // Update resource if necessary
    $resourceModified = false;

    if( isset($resource->status) ) {
      $resourceModified = true;
      $resource->status = $previous->body;
    }

    if( isset($resource->status_date) ) {
      $resourceModified = true;
      $resource->status_date = $previous->creation_date;
    }

    if( isset($resource->status_count) ) {
      $resourceModified = true;
      $resource->status_count = new Zend_Db_Expr('status_count - 1');
    }

    if( $resourceModified ) {
      $resource->save();
    }

    return $this;
  }

  public function getLastStatus(Core_Model_Item_Abstract $resource)
  {
    $select = $this->select();

    if( in_array('resource_type', $this->info('cols')) ) {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select
      ->where('resource_id = ?', $resource->getIdentity())
      ->order('status_id DESC')
      ->limit(1);

    return $this->fetchRow($select);
  }

  public function getStatusSelect(Core_Model_Item_Abstract $resource)
  {
    $select = $this->select();

    if( in_array('resource_type', $this->info('cols')) ) {
      $select->where('resource_type = ?', $resource->getType());
    }
    
    $select
      ->where('resource_id = ?', $resource->getIdentity())
      ->order('status_id DESC');

    return $select;
  }

  public function getStatusPaginator(Core_Model_Item_Abstract $resource)
  {
    return Zend_Paginator::factory($this->getStatusSelect($resource));
  }
}