<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Browse.php 7977 2010-12-07 23:50:10Z char $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Event_Form_Filter_Browse extends Engine_Form
{
  public function init()
  {
    $this->clearDecorators()
      ->addDecorators(array(
        'FormElements',
        array('HtmlTag', array('tag' => 'dl')),
        'Form',
      ))
      ->setMethod('get')
      //->setAttrib('onchange', 'this.submit()')
      ;

    $this->addElement('Select', 'category_id', array(
      'label' => 'Category:',
      'multiOptions' => array(
        '' => 'All Categories',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'dd')),
        array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
      ),
      'onchange' => '$(this).getParent("form").submit();',
    ));

    $this->addElement('Select', 'view', array(
      'label' => 'View:',
      'multiOptions' => array(
        '' => 'Everyone\'s Events',
        '1' => 'Only My Friends\' Events',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'dd')),
        array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
      ),
      'onchange' => '$(this).getParent("form").submit();',
    ));

    $this->addElement('Select', 'order', array(
      'label' => 'List By:',
      'multiOptions' => array(
        'starttime ASC' => 'Start Time',
        'creation_date DESC' => 'Recently Created',
        'member_count DESC' => 'Most Popular',
      ),
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'dd')),
        array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
      ),
      'value' => 'creation_date DESC',
      'onchange' => '$(this).getParent("form").submit();',
    ));
  }
}