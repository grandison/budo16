<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ViewMore.php 8825 2011-04-09 01:34:20Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_ViewMore extends Zend_View_Helper_HtmlElement
{
  protected $_moreLength = 255; // Note: truncation at 255 + 4 = 259 (for " ...")
  protected $_lessLength = 511;
  protected $_maxLength = 1027;
  protected $_fudgesicles = 10;
  protected $_tag = 'span';

  public function viewMore($string, $moreLength = null, $maxLength = null, $lessLength = null, $nl2br = false)
  {
    if( !is_numeric($moreLength) || $moreLength <= 0 ) {
      $moreLength = $this->_moreLength;
    }
    if( !is_numeric($maxLength) || $maxLength <= 0 ) {
      $maxLength = $this->_maxLength;
    }
    if( !is_numeric($lessLength) || $lessLength <= 0 ) {
      $lessLength = $this->_lessLength;
    }

    $strLen = Engine_String::strlen($string);
    if( $strLen <= $moreLength + $this->_fudgesicles ) {
      return $string;
    }
    if( $strLen >= $maxLength ) {
      $strLen = $maxLength;
      $string = Engine_String::substr($string, 0, $maxLength) . $this->view->translate('... &nbsp;');
    }
    
    $shortText = Engine_String::substr($string, 0, $moreLength);
    $fullText = $string;

    if( $nl2br ) {
      $shortText = nl2br($shortText);
      $fullText = nl2br($fullText);
    }

    $content = '<'
      . $this->_tag
      . ' class="view_more"'
      . '>'
      . $shortText
      . $this->view->translate('... &nbsp;')
      . '<a class="view_more_link" href="javascript:void(0);" onclick="$(this).getParent().getNext().style.display=\'\';$(this).getParent().style.display=\'none\';">'.$this->view->translate('more').'</a>'
      . '</'
      . $this->_tag
      . '>'
      . '<'
      . $this->_tag
      . ' class="view_more"'
      . ' style="display:none;"'
      . '>'
      . $fullText
      . ' &nbsp;'
      ;

    if( $strLen >= $lessLength ) {
      $content .= '<a class="view_less_link" href="javascript:void(0);" onclick="$(this).getParent().getPrevious().style.display=\'\';$(this).getParent().style.display=\'none\';">'.$this->view->translate('less').'</a>';
    }

    $content .= '</'
      . $this->_tag
      . '>'
      ;

    return $content;
  }

  public function setMoreLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_moreLength = $length;
    }

    return $this;
  }

  public function setMaxLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_maxLength = $length;
    }

    return $this;
  }
}