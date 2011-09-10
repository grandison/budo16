<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8028 2010-12-10 19:11:19Z char $
 * @author     Jung
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = $('profile_videos').getParent();
    $('profile_videos_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('profile_videos_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('profile_videos_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('profile_videos_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>

<ul id="profile_videos" class="videos_browse">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class="video_thumb_wrapper">
        <?php if ($item->duration):?>
        <span class="video_length">
          <?php
            if( $item->duration>360 ) $duration = gmdate("H:i:s", $item->duration); else $duration = gmdate("i:s", $item->duration);
            if ($duration[0] =='0') $duration = substr($duration,1); echo $duration;
          ?>
        </span>
        <?php endif;?>
        <?php
          if ($item->photo_id) echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.normal'));
          else echo '<img alt="" src="application/modules/Video/externals/images/video.png">';
        ?>
      </div>
      <a class="video_title" href='<?php echo $item->getHref();?>'><?php echo $item->getTitle();?></a>
      <div class="video_author"><?php echo $this->translate('By');?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?></div>
      <div class="video_stats">
        <span class="video_views"><?php echo $item->view_count;?> <?php echo $this->translate('views');?></span>
        <?php if($item->rating>0):?>
          <?php for($x=1; $x<=$item->rating; $x++): ?><span class="rating_star_generic rating_star"></span><?php endfor; ?><?php if((round($item->rating)-$item->rating)>0):?><span class="rating_star_generic rating_star_half"></span><?php endif; ?>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<div>
  <div id="profile_videos_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="profile_videos_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>