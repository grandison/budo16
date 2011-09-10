<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Adcampaign.php 8460 2011-02-15 00:37:15Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_Adcampaign extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getAds()
  {
    $table = Engine_Api::_()->getDbtable('Ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $this->adcampaign_id);
    return $table->fetchAll($select);
  }

  public function getAd()
  {
    $table = Engine_Api::_()->getDbtable('Ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $this->adcampaign_id)->order('views ASC');
    return $table->fetchRow($select);
  }


  
  // Info

  public function isAllowedToView(User_Model_User $user)
  {
    // Check level
    $selectedLevels = Zend_Json::decode($this->level);
    if( !empty($selectedLevels) && is_array($selectedLevels) ) {
      // Get user level
      $levelIdentity = null;
      if( !$user->getIdentity() ) {
        $levelIdentity = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      } else {
        $levelIdentity = $user->level_id;
      }
      if( in_array($levelIdentity, $selectedLevels) ) {
        return true;
      }
    }

    // Check network
    if( $user->getIdentity() ) {
      $selectedNetworks = Zend_Json::decode($this->network);
      if( !empty($selectedNetworks) && is_array($selectedNetworks) ) {
        $userNetworks = Engine_Api::_()->getDbtable('membership', 'network')
            ->getMembershipsOfIds($user, null);
        if( count(array_intersect($userNetworks, $selectedNetworks)) > 0 ) {
          return true;
        }
      }
    }

    return false;
  }

  public function isActive()
  {
    return (
       $this->status &&
       $this->hasStarted() &&
      !$this->hasExpired() &&
      !$this->hasReachedClickLimit() &&
      !$this->hasReachedCtrLimit() &&
      !$this->hasReachedViewLimit()
    );
  }

  public function hasStarted()
  {
    return (time() > strtotime($this->start_time));
  }

  public function hasExpired()
  {
    return ($this->end_settings == 1) && (time() > strtotime($this->end_time));
  }

  public function hasReachedViewLimit()
  {
    return !empty($this->limit_view) &&
        ($this->views >= $this->limit_view);
  }

  public function hasReachedClickLimit()
  {
    return !empty($this->limit_click) &&
        $this->clicks >= $this->limit_click;
  }

  public function hasReachedCtrLimit()
  {
    return !empty($this->limit_ctr) &&
        ($this->views > 0) &&
        ($this->clicks / $this->views * 100) <= $this->limit_ctr;
  }



  // B/c

  public function allowedToView(User_Model_User $user)
  {
    return $this->isAllowedToView($user);
  }

  public function checkLimits()
  {
    return $this->isActive();
  }

  public function checkStarted()
  {
    return !$this->hasStarted();
  }

  public function checkExpired()
  {
    return $this->hasExpired();
  }
}
