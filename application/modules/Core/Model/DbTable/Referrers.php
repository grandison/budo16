<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Referrers.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Referrers extends Engine_Db_Table
{
  public function increment($referrer = null)
  {
    // Check referrer
    if( null === $referrer && !empty($_SERVER['HTTP_REFERER']) ) {
      $referrer = $_SERVER['HTTP_REFERER'];
    }
    if( !$referrer ) {
      return $this;
    }
    $referrer = strtolower($referrer); // @todo not 100% sure this is a great idea

    // Get parts
    $parts = @parse_url($referrer);
    if( !$parts ) {
      return $this;
    }

    extract(array_merge(array(
      'host' => '',
      'path' => '',
      'query' => '',
    ), $parts));

    // Ignore referrers from this host
    if( $host == $_SERVER['HTTP_HOST'] ) {
      return $this;
    }

    // Strip www prefix
    if( strtolower(substr($host, 0, 4)) === 'www.' ) {
      $host = substr($host, 4);
    }

    // Update/insert as necessary
    $updateCount = $this->update(array(
      'value' => new Zend_Db_Expr('value + 1'),
    ), array(
      'host = ?' => $host,
      'path = ?' => $path,
      'query = ?' => $query,
    ));

    if( $updateCount < 1 ) {
      try {
        $this->insert(array(
          'host' => $host,
          'path' => $path,
          'query' => $query,
          'value' => 1,
        ));
      } catch( Exception $e ) {
        // Meh, just ignore
        //throw $e;
      }
    }

    return $this;
  }
}