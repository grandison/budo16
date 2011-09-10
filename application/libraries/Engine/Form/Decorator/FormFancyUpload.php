<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FormFancyUpload.php 7371 2010-09-14 03:33:35Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Form_Decorator_FormFancyUpload extends Zend_Form_Decorator_Abstract
{
  public function render($content)
  {
    $data = $this->getElement()->getAttrib('data');
    if( $data ) {
      $this->getElement()->setAttrib('data', null);
    }
    $view = $this->getElement()->getView();
    return $view->action('upload', 'upload', 'storage', array(
      'name' => $this->getElement()->getName(),
      'data' => $data,
      'element' => $this->getElement()
    ));
  }
}
