<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: RoundRobin.php 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Storage_Form_Admin_Service_RoundRobin extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Get enabled storage services
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $view = Zend_Registry::get('Zend_View');
    
    $multiOptions = array();
    foreach( $serviceTable->fetchAll(array('enabled = ?' => true)) as $service ) {
      $serviceType = $serviceTypesTable->find($service->servicetype_id)->current();
      $multiOptions[$service->service_id] = $view->translate('%1$s (ID: %2$s)',
          $serviceType->title, $service->service_id);
    }

    if( empty($multiOptions) ) {
      $this->addError('This service requires at least one (two recommended) ' .
          'other enabled services.');
      return;
    }

    // Element: adapter
    $this->addElement('MultiCheckbox', 'services', array(
      'label' => 'Services',
      'description' => 'Services to use. Selecting none will use all enabled services.',
      //'required' => true,
      //'allowEmpty' => false,
      'multiOptions' => $multiOptions,
    ));

    parent::init();
  }

  public function isValid($data)
  {
    $valid = parent::isValid($data);

    // Custom valid
    if( $valid ) {
      if( count($this->getElement('services')->options) < 1 ) {
        $this->addError('Requires at least one other enabled service.');
      }
    }

    return $valid;
  }
}