<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Style.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Form_Edit_Style extends Engine_Form
{
  public function init()
  {
    $this->addElement('Textarea', 'style', array(
      'label' => 'Profile Style',
      'description' => 'Add your own CSS code above to give your profile a more personalized look.'
    ));
    $this->style->getDecorator('Description')->setOption('placement', 'APPEND');
    
    $this->addElement('Button', 'done', array(
      'label' => 'Save Styles',
      'type' => 'submit',
    ));
  }
}