<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Video.php 7869 2010-12-01 00:20:30Z shaun $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Model_Video extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';

  protected $_owner_type = 'user';

  protected $_parent_is_owner = true;

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'video_view',
      'reset' => true,
      'user_id' => $this->owner_id,
      'video_id' => $this->video_id,
      'slug' => $this->getSlug(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }
  
  public function getRichContent($view = false, $params = array())
  {
    // if video type is youtube
    if ($this->type == 1){
      $videoEmbedded = $this->compileYouTube($this->code, $view);
    }
    // if video type is vimeo
    if ($this->type == 2){
      $videoEmbedded = $this->compileVimeo($this->code, $view);
    }

    // if video type is uploaded
    if ($this->type ==3){
      $video_location = Engine_Api::_()->storage()->get($this->file_id, $this->getType())->getHref();
      $videoEmbedded = $this->compileFlowPlayer($video_location, $view);
    }

    // $view == false means that this rich content is requested from the activity feed
    if($view==false){
    // prepare the duration
    //
    $video_duration = "";
    if($this->duration){
      if($this->duration>360) $duration = gmdate("H:i:s", $this->duration);
      else $duration = gmdate("i:s", $this->duration);
      if ($duration[0] =='0') $duration= substr($duration,1);
      $video_duration = "<span class='video_length'>".$duration."</span>";
    }
    
    // prepare the thumbnaile
    $thumb = Zend_Registry::get('Zend_View')->itemPhoto($this, 'thumb.video.activity');

    if ($this->photo_id){
      $thumb = Zend_Registry::get('Zend_View')->itemPhoto($this, 'thumb.video.activity');
    }
    else {
      $thumb = '<img alt="" src="application/modules/Video/externals/images/video.png">';
    }

    $thumb = '<a id="video_thumb_'.$this->video_id.'" style="" href="javascript:void(0);" onclick="javascript:var myElement = $(this);myElement.style.display=\'none\';var next = myElement.getNext(); next.style.display=\'block\';">
              <div class="video_thumb_wrapper">'.$video_duration.$thumb.'</div>
              </a>';

    // prepare title and description
    $title = "<a href='".$this->getHref($params)."'>$this->title</a>";
    $tmpBody = strip_tags($this->description);
    $description = "<div class='video_desc'>".(Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody)."</div>";

    $videoEmbedded = $thumb.'<div id="video_object_'.$this->video_id.'" style="display:none;">'.$videoEmbedded.'</div><div class="video_info">'.$title.$description.'</div>';
    }
    
    return $videoEmbedded;
  }

  public function compileYouTube($code, $view){
    //560 x 340
    $embedded = '
    <object width="'.($view?"560":"425").'" height="'.($view?"340":"344").'">
    <param name="movie" value="http://www.youtube.com/v/'.$code.'&color1=0xb1b1b1&color2=0xcfcfcf&hl=en_US&feature=player_embedded&fs=1"/>
    <param name="allowFullScreen" value="true"/>
    <param name="allowScriptAccess" value="always"/>
    <embed src="http://www.youtube.com/v/'.$code.'&color1=0xb1b1b1&color2=0xcfcfcf&hl=en_US&feature=player_embedded&fs=1'.($view?"":"&autoplay=1").'" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="'.($view?"560":"425").'" height="'.($view?"340":"344").'" wmode="transparent"/>
    <param name="wmode" value="transparent" />
    </object>';

    return $embedded;
  }
  public function compileVimeo($code, $view){
    //640 x 360
    $embedded = '
    <object width="'.($view?"640":"400").'" height="'.($view?"360":"230").'">
    <param name="allowfullscreen" value="true"/>
    <param name="allowscriptaccess" value="always"/>
    <param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$code.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" />
    <embed src="http://vimeo.com/moogaloop.swf?clip_id='.$code.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1'.($view?"":"&amp;autoplay=1").'" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="'.($view?"640":"400").'" height="'.($view?"360":"230").'" wmode="transparent"/>
    <param name="wmode" value="transparent" />
    </object>';

    return $embedded;
  }

  public function compileFlowPlayer($location, $view){
    //    php echo $this->baseUrl() /externals/flowplayer/flowplayer-3.1.5.swf"
    $embedded = "
    <script type='text/javascript'>
    en4.core.runonce.add(function(){\$('video_thumb_".$this->video_id."').removeEvents('click').addEvent('click', function(){flashembed('video_object_$this->video_id',{src: '".Zend_Registry::get('Zend_View')->baseUrl()."/externals/flowplayer/flowplayer-3.1.5.swf', width: ".($view?"480":"420").", height: ".($view?"386":"326").", wmode: 'transparent'},{config: {clip: {url: '$location',autoPlay: ".($view?"false":"true").", duration: '$this->duration', autoBuffering: true},plugins: {controls: {background: '#000000',bufferColor: '#333333',progressColor: '#444444',buttonColor: '#444444',buttonOverColor: '#666666'}},canvas: {backgroundColor:'#000000'}}});})});
    </script>";

    return $embedded;
  }
  
  public function getKeywords($separator = ' ')
  {
    $keywords = array();
    foreach( $this->tags()->getTagMaps() as $tagmap ) {
      $tag = $tagmap->getTag();
      $keywords[] = $tag->getTitle();
    }

    if( null === $separator ) {
      return $keywords;
    }

    return join($separator, $keywords);
  }
  
  // Interfaces

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   **/
  public function comments()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }
  
  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   **/
  public function tags()
  {
    return new Engine_ProxyObject($this, $this->api()->getDbtable('tags', 'core'));
  }
}