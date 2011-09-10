<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8303 2011-01-25 09:20:19Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Storage_Form_Admin_Service_Create extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Add Storage Service')
      ->setDescription('You will be taken to the edit page after creation ' .
          'to specify any extra settings that are required.');

    // Element: servicetype_id
    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $multiOptions = array();
    foreach( $serviceTypesTable->fetchAll() as $serviceType ) {
      $multiOptions[$serviceType->servicetype_id] = $serviceType->title;
    }
    $this->addElement('Select', 'servicetype_id', array(
      'label' => 'Service Type',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => $multiOptions,
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Add Service',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
      'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      )
    ));
  }
}