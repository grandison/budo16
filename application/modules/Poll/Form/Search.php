<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Search.php 7443 2010-09-22 07:25:41Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Poll_Form_Search extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('page' => null)))
      ;

    parent::init();
    
    $this->addElement('Text', 'search', array(
      'label' => 'Search Polls:',
    ));

    $this->addElement('Select', 'show', array(
      'label' => 'Show',
      'multiOptions' => array(
        '1' => 'Everyone\'s Polls',
        '2' => 'Only My Friends\' Polls',
      ),
      'onchange' => 'searchPolls();',
    ));
    
    $this->addElement('Select', 'closed', array(
      'label' => 'Status',
      'onchange' => 'searchPolls();',
      'multiOptions' => array(
        '' => 'All Polls',
        '0' => 'Only Open Polls',
        '1' => 'Only Closed Polls',
      ),
    ));

    $this->addElement('Select', 'order', array(
      'label' => 'Browse By:',
      'onchange' => 'searchPolls();',
      'multiOptions' => array(
        'recent' => 'Most Recent',
        'popular' => 'Most Popular',
      ),
    ));
  }
}