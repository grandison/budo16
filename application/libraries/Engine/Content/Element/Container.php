<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Container.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Content_Element_Container extends Engine_Content_Element_Abstract
{
  public function loadDefaultDecorators()
  {
    if( !empty($this->_decorators) ) {
      return;
    }
    
    // Add decorators
    $this
      ->addDecorator('Children')
      ->addDecorator('Container');
  }
  
  protected function _render()
  {
    return '';
  }
}