<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Abstract.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Authorization_Form_Admin_Level_Abstract extends Engine_Form
{
  protected $_moderator;

  protected $_public;

  public function setModerator($moderator)
  {
    $this->_moderator = (bool) $moderator;
    return $this;
  }

  public function isModerator()
  {
    return (bool) $this->_moderator;
  }

  public function setPublic($public)
  {
    $this->_public = (bool) $public;
    return $this;
  }

  public function isPublic()
  {
    return (bool) $this->_public;
  }

  public function init()
  {
    // Change description decorator
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOptions(array('tag' => 'h4', 'placement' => 'PREPEND'));

    // Prepare user levels
    $levelOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      $levelOptions[$level->level_id] = $level->getTitle();
    }

    // Element: level_id
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'multiOptions' => $levelOptions,
      'onchange' => 'javascript:fetchLevelSettings(this.value);',
      'ignore' => true,
    ));




    // Add submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'order' => 100000,
    ));
  }
}