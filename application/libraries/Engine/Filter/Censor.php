<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Censor.php 8919 2011-05-06 23:39:22Z shaun $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Filter_Censor implements Zend_Filter_Interface
{
  protected static $_defaultForbiddenWords;
  
  protected $_forbiddenWords;

  protected $_replaceString = '*';
  
  public function __construct($options = array())
  {
    if( !empty($options['forbiddenWords']) ) {
      $this->_forbiddenWords = $options['forbiddenWords'];
    } else {
      $this->_forbiddenWords = self::$_defaultForbiddenWords;
    }
    if( is_string($this->_forbiddenWords) ) {
      $this->_forbiddenWords = preg_split('/\s*,\s*/', $this->_forbiddenWords);
      $this->_forbiddenWords = array_map('trim', $this->_forbiddenWords);
      $this->_forbiddenWords = array_filter($this->_forbiddenWords);
    }
    if( !is_array($this->_forbiddenWords) ) {
      $this->_forbiddenWords = null;
    }

    if( !empty($options['replaceString']) ) {
      $this->_replaceString = $options['replaceString'];
    }
  }

  public function filter($value)
  {
    if( empty($value) || empty($this->_forbiddenWords) || !is_array($this->_forbiddenWords) ) {
      return $value;
    }
    
    foreach( $this->_forbiddenWords as $word ) {
      $replace = str_pad('', strlen($word), $this->_replaceString);
      $value = preg_replace('/\b' . $word. '\b/i', $replace, $value);
    }
    
    return $value;
  }

  // Static stuff

  public static function setDefaultForbiddenWords($words)
  {
    self::$_defaultForbiddenWords = $words;
  }
}