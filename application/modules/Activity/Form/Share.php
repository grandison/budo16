<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Share.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Form_Share extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Share')
      ->setDescription('Share this by re-posting it with your own message.')
      ->setMethod('POST')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    
    $this->addElement('Textarea', 'body', array(
      //'required' => true,
      //'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_EnableLinks(),
        new Engine_Filter_Censor(),
      ),
    ));

    // Buttons
    $buttons = array();
    
    if ('publish' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable && User_Model_DbTable_Facebook::getFBInstance()->getSession()) {
      $this->addElement('Dummy', 'post_to_facebook', array(
        'content' => '
          <span href="javascript:void(0);" class="composer_facebook_toggle" onclick="toggleFacebookShareCheckbox();">
            <span class="composer_facebook_tooltip">
              Publish this on Facebook
            </span>
            <input type="checkbox" name="post_to_facebook" value="1" style="display:none;">
          </span>',
      ));
      $this->getElement('post_to_facebook')->clearDecorators();
      $buttons[] = 'post_to_facebook';
    }


    $this->addElement('Button', 'submit', array(
      'label' => 'Share',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
    $buttons[] = 'submit';

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $buttons[] = 'cancel';


    $this->addDisplayGroup($buttons, 'buttons');
    $button_group = $this->getDisplayGroup('buttons');

  }
}