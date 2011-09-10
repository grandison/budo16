<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8444 2011-02-11 23:28:10Z steve $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Form_Topic_Create extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->setMethod("POST");
    $this->setAttrib('name', 'forum_post_create');
    $this->addElement('Text', 'title', array(
      'label' => 'Topic Title',
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
      'validators' => array(
        array('StringLength', true, array(1, 64)),
      ),
    ));
    $viewer = Engine_Api::_()->user()->getViewer();

    $filter = new Engine_Filter_Html();
    $allowed_tags = explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'forum', 'commentHtml'));

    if( $settings->getSetting('forum_html', 0) == '0' ) {
      $filter->setForbiddenTags();
      $filter->setAllowedTags($allowed_tags);
    }
    if( ($settings->getSetting('forum_html', 0) == '1') || ($settings->getSetting('forum_bbcode', 0) == '1') ) {
      $this->addElement('TinyMce', 'body', array(
        'disableLoadDefaultDecorators' => true,
        'editorOptions' => array(
          'bbcode' => $settings->getSetting('forum_bbcode', 0),
          'html' => $settings->getSetting('forum_html', 0)
        ),
        'required' => true,
        'allowEmpty' => false,
        'decorators' => array('ViewHelper'),
        'filters' => array(
          $filter,
          new Engine_Filter_Censor(),
        ),
      ));
    } else {
      $this->addElement('textarea', 'body', array(
        'required' => true,
        'attribs' => array('rows' => 24, 'cols' => 80, 'style' => 'width:553px; max-width:553px;height:158px;'),
        'allowEmpty' => false,
        'filters' => array(
          $filter,
          new Engine_Filter_Censor(),
        ),
      ));
    }

    // Photo
    $this->addElement('File', 'photo', array(
      'label' => '<a id="photo-label" href="javascript:showUploader();">Attach a Photo</a>',
      'size' => '40',
      'attribs' => array('style' => 'display:none;')
    ));
    $this->getElement('photo')->getDecorator('label')->setOptions(array('escape' => false, 'class' => 'buttonlink'));

    $this->addElement('Checkbox', 'watch', array(
      'label' => 'Send me notifications when other members reply to this topic.',
      'value' => '1',
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Post Topic',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
    $button_group->addDecorator('DivDivDivWrapper');
  }
}