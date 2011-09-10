<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Quick.php 7481 2010-09-27 08:41:01Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Form_Post_Quick extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Quick Reply')
      ->setAttrib('name', 'forum_post_quick')
      ;
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();

    $filter = new Engine_Filter_Html();
    $allowed_tags = explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'forum', 'commentHtml'));

    if( $settings->getSetting('forum_html', 0) == '0' ) {
      $filter->setForbiddenTags();
      $filter->setAllowedTags($allowed_tags);
    }

    // Element: body
    $this->addElement('textarea', 'body', array(
      'label' => 'Quick Reply',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        $filter,
        new Engine_Filter_Censor(),
      ),
    ));

    // Element: photo
    // Need this hack for some reason
    $this->addElement('File', 'photo', array(
      'attribs' => array('style' => 'display:none;')
    ));

    // Element: watch
    $this->addElement('Checkbox', 'watch', array(
      'label' => 'Send me notifications when other members reply to this topic.',
      'value' => '1',
    ));

    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Post Reply',
      'type' => 'submit',
    ));
  }
}