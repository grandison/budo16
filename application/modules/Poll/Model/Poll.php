<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Poll.php 7507 2010-10-01 00:13:15Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Model_Poll extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';

  protected $_parent_is_owner = true;

  
  // Interfaces
  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'poll_view',
      'reset' => true,
      'user_id' => $this->user_id,
      'poll_id' => $this->poll_id,
      'slug' => $this->getSlug(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getHiddenSearchData()
  {
    $optionsTable = Engine_Api::_()->getDbTable('options', 'poll');
    $options = $optionsTable
      ->select()
      ->from($optionsTable->info('name'), 'poll_option')
      ->where('poll_id = ?', $this->getIdentity())
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);

    return join(' ', $options);
  }

  public function getRichContent()
  {
    $view = Zend_Registry::get('Zend_View');
    $view = clone $view;
    $view->clearVars();
    $view->addScriptPath('application/modules/Poll/views/scripts/');
    
    $content = '';
    $content .= '
      <div class="feed_poll_rich_content">
        <div class="feed_item_link_title">
          ' . $view->htmlLink($this->getHref(), $this->getTitle()) . '
        </div>
        <div class="feed_item_link_desc">
          ' . $view->viewMore($this->getDescription()) . '
        </div>
    ';

    // Render the thingy
    $view->poll = $this;
    $view->owner = $owner = $this->getOwner();
    $view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $view->pollOptions = $this->getOptions();
    $view->hasVoted = $this->viewerVoted();
    $view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showPieChart', false);
    $view->canVote = $this->authorization()->isAllowed(null, 'vote');
    $view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canChangeVote', false);
    $view->hideLinks = true;

    $content .= $view->render('_poll.tpl');

    /* $content .= '
    <div class="poll_stats">
    '; */

    $content .= '
      </div>
    ';
    return $content;
  }
  
  public function getOptions()
  {
    return Engine_Api::_()->getDbtable('options', 'poll')->fetchAll(array(
      'poll_id = ?' => $this->getIdentity(),
    ));
  }

  public function hasVoted(User_Model_User $user)
  {
    $table = Engine_Api::_()->getDbtable('votes', 'poll');
    return (bool) $table
      ->select()
      ->from($table, 'COUNT(*)')
      ->where('poll_id = ?', $this->getIdentity())
      ->where('user_id = ?', $user->getIdentity())
      ->query()
      ->fetchColumn(0)
      ;
  }

  public function getVote(User_Model_User $user)
  {
    $table = Engine_Api::_()->getDbtable('votes', 'poll');
    return $table
      ->select()
      ->from($table, 'poll_option_id')
      ->where('poll_id = ?', $this->getIdentity())
      ->where('user_id = ?', $user->getIdentity())
      ->query()
      ->fetchColumn(0)
      ;
  }

  public function getVoteCount($recheck = false)
  {
    if( $recheck ) {
      $table = Engine_Api::_()->getDbtable('votes', 'poll');
      $voteCount = $table->select()
        ->from($table, 'COUNT(*)')
        ->where('poll_id = ?', $this->getIdentity())
        ->query()
        ->fetchColumn(0)
        ;
      if( $voteCount != $this->vote_count ) {
        $this->vote_count = $voteCount;
        $this->save();
      }
    }
    return $this->vote_count;
  }

  public function viewerVoted()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    return $this->getVote($viewer);
  }

  public function vote(User_Model_User $user, $option)
  {
    $table = Engine_Api::_()->getDbTable('votes', 'poll');

    $row = $table->fetchRow(array(
      'poll_id = ?' => $this->getIdentity(),
      'user_id = ?' => $user->getIdentity(),
    ));

    if( null === $row ) {
      $row = $table->createRow();
      $row->setFromArray(array(
        'poll_id' => $this->getIdentity(),
        'user_id' => $user->getIdentity(),
        'creation_date' => date("Y-m-d H:i:s"),
      ));
    }
    
    $row->poll_option_id = $option;
    $row->modified_date  = date("Y-m-d H:i:s");
    $row->save();

    // We also have to update the poll_options table
    $optionsTable = Engine_Api::_()->getDbtable('options', 'poll');
    $optionsTable->update(array(
      'votes' => new Zend_Db_Expr('votes + 1'),
    ), array(
      'poll_id = ?' => $this->getIdentity(),
      'poll_option_id = ?' => $option,
    ));

    // Recheck all options?
    /*
    // Note: this doesn't seem to work because we're in a transaction -_-
    $subselect = $table->select()
      ->from($table, 'COUNT(*)')
      ->where('poll_id = ?', $this->getIdentity())
      ->where('poll_option_id = ?', new Zend_Db_Expr($optionsTable->info('name') . '.poll_option_id'))
      ;
    $optionsTable->update(array(
      'votes' => $subselect,
    ), array(
      'poll_id = ?' => $this->getIdentity(),
    ));
     * 
     */

    // Update internal vote count
    $this->vote_count = new Zend_Db_Expr('vote_count + 1');
    $this->save();
  }

  protected function _insert()
  {
    if( null === $this->search ) {
      $this->search = 1;
    }

    parent::_insert();
  }

  protected function _delete()
  {
    // delete poll votes
    Engine_Api::_()->getDbtable('votes', 'poll')->delete(array(
      'poll_id = ?' => $this->getIdentity(),
    ));

    // delete poll options
    Engine_Api::_()->getDbtable('options', 'poll')->delete(array(
      'poll_id = ?' => $this->getIdentity(),
    ));

    parent::_delete();
  }

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   **/
  public function comments()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }
}