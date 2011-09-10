<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: ReplaceLinks.php 7244 2010-09-01 01:49:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_ReplaceLinks extends Engine_View_Helper_HtmlLink
{
  protected $_attribs;

  public function replaceLinks($string, $attribs = array())
  {
    // Set attribs
    $attribs = array_merge(array(
      'target' => '_blank',
      'rel' => 'nofollow',
    ), $attribs);
    $this->_attribs = $attribs;

    // Replace and return
    return preg_replace_callback('/http\S+/i', array($this, '_replace'), $value);
  }
  
  protected function _replace($matches)
  {
    return $this->htmlLink($matches[0], $matches[0], $this->_attribs);
  }
}