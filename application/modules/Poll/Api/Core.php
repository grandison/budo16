<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8368 2011-02-01 03:59:27Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Api_Core extends Core_Api_Abstract
{
  public function getPollSelect($params = array())
  {
    // Setup
    $params = array_merge(array(
      'user_id' => null,
      'order' => 'recent',
      'search' => '',
      'closed' => 0,
    ), $params);

    $table = Engine_Api::_()->getItemTable('poll');
    $tableName = $table->info('name');

    $select = $table
      ->select()
      ->from($tableName)
      ;

    // Browse
    if( isset($params['browse']) ) {
      $select->where('search = ?', (int) (bool) $params['browse']);
    }

    // Closed
    if( !isset($params['closed']) || null === $params['closed'] ) {
      $params['closed'] = 0;
    }
    $select
      ->where('is_closed = ?', $params['closed']);

    // User
    if( !empty($params['user_id']) ) {
      $select
        ->where('user_id = ?', $params['user_id']);
    } else if( !empty($params['users']) && is_array($params['users']) ) {
      $select
        ->where('user_id IN(?)', $params['users']);
    }

    // Order
    switch( $params['order'] ) {
      case 'popular':
        $select
          ->order('vote_count DESC')
          ->order('view_count DESC');
        break;
      case 'recent':
      default:
        $select
          ->order('creation_date DESC');
        break;
    }

    if( !empty($params['search']) ) {
      // Add search table
      $searchTable = Engine_Api::_()->getDbtable('search', 'core');
      $db = $searchTable->getAdapter();
      $sName = $searchTable->info('name');
      $rName = $tableName;
      $select
        ->joinRight($sName, $sName . '.id=' . $rName . '.poll_id', null)
        ->where($sName . '.type = ?', 'poll')
        ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['search'])))
        //->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['text'])))
        ;

      // Add options table?
      // Note: I added indexing of poll options in 4.0.5, so this shouldn't be necessary
      /*
      $search = "%{$params['search']}%";
      // if we do not need to search the Options table, we could just do this:
      // ->where("`title` LIKE ? OR `description` LIKE ?", $search);
      // but since we do, we must do the following join:
      if( 'popular' != $sort ) {
        $table_options = Engine_Api::_()->getDbTable('options', 'poll')->info('name');
        $select->joinLeft($o_name, "$p_name.poll_id = $o_name.poll_id", '')
            ->where("`title` LIKE ? OR `description` LIKE ? OR $o_name.poll_option LIKE ?", $search)
            ->group("$p_name.poll_id");
      } else
          $select->where("`title` LIKE ? OR `description` LIKE ? OR $o_name.poll_option LIKE ?", $search);
       * 
       */
    }

    return $select;
  }

  /**
   * Gets a paginator for polls
   *
   * @param Core_Model_Item_Abstract $user The user to get the messages for
   * @return Zend_Paginator
   */
  public function getPollsPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getPollSelect($params));
  }
}