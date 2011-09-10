<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 7374 2010-09-14 05:02:38Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Form_Edit extends Classified_Form_Create
{
  public $_error = array();
  protected $_item;

  public function getItem()
  {
    return $this->_item;
  }

  public function setItem(Core_Model_Item_Abstract $item)
  {
    $this->_item = $item;
    return $this;
  }
  
  public function init()
  {
    parent::init();

    
    $this->setTitle('Edit Classified Listing')
         ->setDescription('Edit your listing below, then click \"Save Changes\" to save your listing.');
    $this->addElement('Radio', 'cover', array(
      'label' => 'Album Cover',
    ));
    $this->execute->setLabel('Save Changes');
  }
}