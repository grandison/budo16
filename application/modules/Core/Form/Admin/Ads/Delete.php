<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Delete.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Ads_Delete extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Delete Ad Campaign')
      ->setDescription('Are you sure you want to delete this ad campaign? All the advertisements associated with this campaign will also be deleted.');
    
    $ad_id = new Zend_Form_Element_Hidden('adcampaign_id');
    $ad_id
      //->clearDecorators()
      //->addDecorator('ViewHelper');
      ->addValidator('Int');

    $this->addElements(array(
      $ad_id
    ));
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Delete Campaign',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

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
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}