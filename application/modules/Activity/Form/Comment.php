<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Comment.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Activity_Form_Comment extends Engine_Form
{
  public function init()
  {
    $this->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->setAttrib('class', null)
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'activity',
          'controller' => 'index',
          'action' => 'comment',
        ), 'default'));

    //$allowed_html = Engine_Api::_()->getApi('settings', 'core')->core_general_commenthtml;
    $viewer = Engine_Api::_()->user()->getViewer();
    $allowed_html = "";
    if($viewer->getIdentity()){
      $allowed_html = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'commentHtml');
    }
    $this->addElement('Textarea', 'body', array(
      'rows' => 1,
      'decorators' => array(
        'ViewHelper'
      ),
      'filters' => array(
        new Engine_Filter_Html(array('AllowedTags'=>$allowed_html)),
        //new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_EnableLinks(),
        new Engine_Filter_Censor(),
      ),
    ));

    if (Engine_Api::_()->getApi('settings', 'core')->core_spam_comment) {
      $this->addElement('captcha', 'captcha', array(
        'description' => 'Please type the characters you see in the image.',
        'captcha' => 'image',
        'required' => true,
        'captchaOptions' => array(
          'wordLen' => 6,
          'fontSize' => '30',
          'timeout' => 300,
          'imgDir' => APPLICATION_PATH . '/public/temporary/',
          'imgUrl' => $this->getView()->baseUrl().'/public/temporary',
          'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf'
        )));
    }

    $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'ignore' => true,
      'label' => 'Post Comment',
      'decorators' => array(
        'ViewHelper',
      )
    ));
    
    $this->addElement('Hidden', 'action_id', array(
      'order' => 990,
      'filters' => array(
        'Int'
      ),
    ));

    $this->addElement('Hidden', 'return_url', array(
      'order' => 991,
      'value' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array())
    ));
  }

  public function setActionIdentity($action_id)
  {
    $this
      ->setAttrib('id', 'activity-comment-form-'.$action_id)
      ->setAttrib('class', 'activity-comment-form')
      ->setAttrib('style', 'display: none;');
    $this->action_id
      ->setValue($action_id)
      ->setAttrib('id', 'activity-comment-id-'.$action_id);
    $this->submit //->getDecorator('HtmlTag')
      ->setAttrib('id', 'activity-comment-submit-'.$action_id)
      ;

    $this->body
      ->setAttrib('id', 'activity-comment-body-'.$action_id)
      ;
      //->setAttrib('onfocus', "document.getElementById('activity-comment-submit-".$action_id."').style.display = 'block';")
      //->setAttrib('onblur', "if( this.value == '' ) { document.getElementById('activity-comment-form-".$action_id."').style.display = 'none'; }");

    return $this;
  }

  public function renderFor($action_id)
  {
    return $this->setActionIdentity($action_id)->render();
  }
}