<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Generic.php 8292 2011-01-25 00:21:31Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Form_Admin_Job_Generic extends Engine_Form
{
  public function init()
  {
    $this->addElement('Button', 'execute', array(
      'label' => 'Add',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}