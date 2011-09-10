<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Mysqli.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Db_Export_Mysqli extends Engine_Db_Export_Mysql
{
  protected function _queryRaw($sql)
  {
    $connection = $this->getAdapter()->getConnection();

    if( !($result = $connection->query($sql)) ) {
      throw new Engine_Db_Export_Exception('Unable to execute raw query.');
    }
    
    $data = array();
    while( false != ($row = $result->fetch_assoc()) ) {
      $data[] = $row;
    }
    
    return $data;
  }
}