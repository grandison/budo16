<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: EnableLinks.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Filter_EnableLinks implements Zend_Filter_Interface
{
  /**
   *
   * @var string
   */
  protected $_class;

  /**
   *
   * @var Zend_View_Abstract
   */
  protected $_view;

  /**
   * Constructor
   * 
   * @param array $options
   */
  public function __construct($options = array())
  {
    if( !empty($options['class']) )
    {
      $this->_class = $options['class'];
    }

    if( !empty($options['view']) )
    {
      $this->_view = $options['view'];
    }

    else if( Zend_Registry::isRegistered('Zend_View') )
    {
      $this->_view = Zend_Registry::get('Zend_View');
    }
  }

  /**
   * Replace normal links with html links
   * @param string $value
   * @return string
   */
  public function filter($value)
  {
    return preg_replace_callback('/http\S+/i', array($this, '_replace'), $value);
  }

  /**
   * Does the hard work for preg_replace_callback() in self::filter()
   * 
   * @param string $matches
   * @return string
   */
  protected function _replace($matches)
  {
    if( $this->_view instanceof Zend_View_Abstract )
    {
      $href = $this->_view->escape($matches[0]);
    }

    else
    {
      $href = htmlspecialchars($matches[0]);
    }
    
    return '<a'
      . ' href="' . $href . '"'
      . ( null !== $this->_class ? ' class="'.$this->_class.'"' : '' )
      . ' target="_blank" rel="nofollow"'
      . '>'
      . $matches[0]
      . '</a>'
      ;
  }
}
