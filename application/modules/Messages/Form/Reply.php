<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Reply.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Messages_Form_Reply extends Engine_Form
{
  public function init()
  {
    $this
      //->setAttrib('class', 'global_form_box')
      //->setDecorators(array('FormElements', 'Form'))
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
      
    // Init body
    $this->addElement('Textarea', 'body', array(
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Reply',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}