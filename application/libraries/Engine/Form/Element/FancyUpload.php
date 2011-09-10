<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FancyUpload.php 7371 2010-09-14 03:33:35Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Form_Element_FancyUpload extends Zend_Form_Element_Hidden
{
  public function getValue()
  {
    return explode(" ", trim($this->_value));
  }
  
  public function render(Zend_View_Interface $view = null)
  {
      if (null !== $view) {
          $this->setView($view);
      }

      $content = '';
      foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
      }
      return $content;
  }
  
  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
    if( $this->loadDefaultDecoratorsIsDisabled() )
    {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) )
    {
      $this->addDecorator('FormFancyUpload');
      Engine_Form::addDefaultDecorators($this);
    }
  }
}
