<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: List.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_Item_List extends Engine_Db_Table_Rowset
{
  public function __call($method, array $args)
  {
    $return = array();
    $isSelf = null;

    foreach( $this as $row ) {
      $r = new ReflectionMethod($row, $method);
      $ret = $r->invokeArgs($row, $args);
      if( null === $isSelf ) {
        if( $ret === $row ) {
          $isSelf = true;
        }
      } else if( !$isSelf ) {
        $return[] = $ret;
      }
    }

    if( $isSelf ) {
      return $this;
    }

    return $return;
  }

  public function __get($key)
  {
    $return = array();

    foreach( $this as $row ) {
      $return[] = $row->$key;
    }
    
    return $return;
  }

  public function __set($key, $value)
  {
    foreach( $this as $row ) {
      $row->$key = $value;
    }
    return $this;
  }

  public function __isset($key)
  {
    $set = true;
    foreach( $this as $row ) {
      $set &= isset($row->$key);
    }
    return $set && $this->count();
  }

  public function __unset($key)
  {
    foreach( $this as $row ) {
      unset($row->$key);
    }
  }
}