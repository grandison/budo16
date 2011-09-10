<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Api_Core extends Core_Api_Abstract
{
  /**
   * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
   */
  protected $_subject;
  
  /**
   * Set the object that represents the subject of the page
   *
   * @param Core_Model_Item_Abstract|mixed $subject
   * @return Core_Model_Api
   */
  public function setSubject($subject)
  {
    if( null !== $this->_subject )
    {
      throw new Core_Model_Exception("The subject may not be set twice");
    }

    if( !($subject instanceof Core_Model_Item_Abstract) )
    {
      throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
    }
    
    $this->_subject = $subject;
    return $this;
  }

  /**
   * Get the previously set subject of the page
   *
   * @return Core_Model_Item_Abstract|null
   */
  public function getSubject($type = null)
  {
    if( null === $this->_subject )
    {
      throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
    }
    else if( is_string($type) && $type !== $this->_subject->getType() )
    {
      throw new Core_Model_Exception("getSubject was given a type other than the set subject");
    }
    else if( is_array($type) && !in_array($this->_subject->getType(), $type) )
    {
      throw new Core_Model_Exception("getSubject was given a type other than the set subject");
    }
    
    return $this->_subject;
  }

  /**
   * Checks if a subject has been set
   *
   * @return bool
   */
  public function hasSubject($type = null)
  {
    if( null === $this->_subject ) {
      return false;
    } else if( null === $type ) {
      return true;
    } else {
      return ( $type === $this->_subject->getType() );
    }
  }

  public function clearSubject()
  {
    $this->_subject = null;
    return $this;
  }
}