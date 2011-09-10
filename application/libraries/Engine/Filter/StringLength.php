<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: StringLength.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Filter_StringLength implements Zend_Filter_Interface
{
  protected $_maxLength;
  
  public function __construct($options = array())
  {
    if( !empty($options['max']) )
    {
      $this->_maxLength = $options['max'];
    }
  }

  public function filter($value)
  {
    return Engine_String::substr($value, 0, $this->_maxLength);
  }
}