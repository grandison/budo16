<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Widget_Ads extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();

    // Set form attributes
    $this
      ->setTitle('Ad Campaign')
      ->setDescription('Please choose an advertisement campaign.')
      ->setAttrib('id', 'form-upload');
      
    $campaigns = Engine_Api::_()->getDbtable('adcampaigns', 'core')->fetchAll();

    if( count($campaigns) > 0 ) {
      // Element: adcampaign_id
      $this->addElement('Select', 'adcampaign_id', array(
        'label' => 'Ad Campaign',
      ));
      
      $this->adcampaign_id->addMultiOption(0, '');
      foreach( $campaigns as $campaign ) {
        $this->adcampaign_id->addMultiOption($campaign->adcampaign_id, $campaign->name);
      }
    }
  }
}