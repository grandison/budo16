<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Category.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Blog_Model_Category extends Core_Model_Item_Abstract
{
  // Properties


  // General

  public function getTable()
  {
    if( is_null($this->_table) )
    {
      $this->_table = Engine_Api::_()->getDbtable('categories', 'blog');
    }

    return $this->_table;
  }


  public function getUsedCount(){
    $table  = Engine_Api::_()->getDbTable('blogs', 'blog');
    $rName = $table->info('name');
    $select = $table->select()
                    ->from($rName)
                    ->where($rName.'.category_id = ?', $this->category_id);
    $row = $table->fetchAll($select);
    $total = count($row);
    return $total;
  }
  
  // Ownership

  public function isOwner($owner)
  {
    if( $owner instanceof Core_Model_Item_Abstract )
    {
      return ( $this->getIdentity() == $owner->getIdentity() && $this->getType() == $owner->getType() );
    }

    else if( is_array($owner) && count($owner) === 2 )
    {
      return ( $this->getIdentity() == $owner[1] && $this->getType() == $owner[0] );
    }

    else if( is_numeric($owner) )
    {
      return ( $owner == $this->getIdentity() );
    }

    return false;
  }

  public function getOwner()
  {
    return $this;
  }
}