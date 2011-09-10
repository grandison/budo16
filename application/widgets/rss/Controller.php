<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Rss
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8448 2011-02-12 01:08:24Z john $
 * @author     John
 */

/**
 * @category   Application_Widget
 * @package    Rss
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Widget_RssController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $url = $this->_getParam('url');
    if( !$url ) {
      return $this->setNoRender();
    }
    $this->view->url = $url;

    // Zend_Feed requires DOMDocument
    if( !class_exists('DOMDocument', false) ) {
      return $this->setNoRender();
    }

    // Parse feed
    $rss = Zend_Feed::import($url);

    // Prepare channel info
    $channel = array(
      'title'       => $rss->title(),
      'link'        => null,
      'description' => $rss->description(),
      'items'       => array()
    );

    // Get link
    $link = $rss->link('self');
    if( $link ) {
      if( $link instanceof DOMElement ) {
        $channel['link'] = $link->nodeValue;
      } else if( is_array($link) ) {
        foreach( $link as $subLink ) {
          $channel['link'] = $subLink->nodeValue;
          if( !empty($channel['link']) ) {
            break;
          }
        }
      }
    }

    // Get items
    $this->view->max = $max = $this->_getParam('max', 4);
    $this->view->strip = $strip = $this->_getParam('strip', true);
    $count = 0;
    
    // Loop over each channel item and store relevant data
    foreach( $rss as $item ) {
      if( $count++ >= $max ) break;
      $channel['items'][] = array(
        'title'       => $item->title(),
        'link'        => $item->link(),
        'description' => $item->description(),
        'pubDate'     => $item->pubDate(),
        'guid'        => $item->guid(),
      );
    }

    $this->view->channel = $channel;
  }
}