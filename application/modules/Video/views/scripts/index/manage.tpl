<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manage.tpl 8089 2010-12-21 01:38:39Z john $
 * @author     Jung
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Videos');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<div class='layout_right'>
  <?php echo $this->form->render($this) ?>
  <?php if($this->can_create):?>
  <div class="quicklinks">
    <ul>
      <li>
      <?php echo $this->htmlLink(array('route' => 'video_general', 'action' => 'create'), $this->translate('Post New Video'), array(
        'class' => 'buttonlink icon_video_new'
      )) ?>
     </ul>
  </div>
  <?php endif ?>
</div>

<div class='layout_middle'>
<?php if (($this->current_count >= $this->quota) && !empty($this->quota)):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('You have already created the maximum number of videos allowed. If you would like to post a new video, please delete an old one first.');?>
    </span>
  </div>
  <br/>
<?php endif; ?>

  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

  <ul class='videos_manage'>
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
      <div class='video_options'>
          <?php echo $this->htmlLink(array('route' => 'video_edit', 'video_id' => $item->video_id), $this->translate('Edit Video'), array(
            'class' => 'buttonlink icon_video_edit'
          )) ?>
          <?php
          if ($item->status !=2){
            echo $this->htmlLink(array('route' => 'default', 'module' => 'video', 'controller' => 'index', 'action' => 'delete', 'video_id' => $item->video_id, 'format' => 'smoothbox'), $this->translate('Delete Video'), array(
              'class' => 'buttonlink smoothbox icon_video_delete'
            ));
          }
          ?>
      </div>
      <div class="video_info">
        <h3>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
        </h3>
        <div class="video_desc">
          <?php echo $this->string()->truncate($this->string()->stripTags($item->description), 300) ?>
        </div>
        <div class="video_stats">
          <span class="video_views"><?php echo $this->translate('Added');?> <?php echo $this->timestamp(strtotime($item->creation_date)) ?> - <?php echo $this->translate(array('%s comment', '%s comments', $item->comments()->getCommentCount()),$this->locale()->toNumber($item->comments()->getCommentCount())) ?> - <?php echo $this->translate(array('%s like', '%s likes', $item->likes()->getLikeCount()),$this->locale()->toNumber($item->likes()->getLikeCount())) ?> - <?php echo $this->translate(array('%s view', '%s views', $item->view_count),$this->locale()->toNumber($item->view_count)) ?></span>
          <span class="video_star"></span><span class="video_star"></span><span class="video_star"></span><span class="video_star"></span><span class="video_star_half"></span>
        </div>
        <?php if($item->status == 0):?>
          <div class="tip">
            <span>
              <?php echo $this->translate('Your video is in queue to be processed - you will be notified when it is ready to be viewed.')?>
            </span>
          </div>
        <?php elseif($item->status == 2):?>
          <div class="tip">
            <span>
              <?php echo $this->translate('Your video is currently being processed - you will be notified when it is ready to be viewed.')?>
            </span>
          </div>
        <?php elseif($item->status == 3):?>
          <div class="tip">
            <span>
             <?php echo $this->translate('Video conversion failed. Please try %1$suploading again%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=>3)).'">', '</a>'); ?>
            </span>
          </div>
        <?php elseif($item->status == 4):?>
          <div class="tip">
            <span>
             <?php echo $this->translate('Video conversion failed. Video format is not supported by FFMPEG. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=>3)).'">', '</a>'); ?>
            </span>
          </div>
         <?php elseif($item->status == 5):?>
          <div class="tip">
            <span>
             <?php echo $this->translate('Video conversion failed. Audio files are not supported. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=>3)).'">', '</a>'); ?>

            </span>
          </div>
         <?php elseif($item->status == 7):?>
          <div class="tip">
            <span>
             <?php echo $this->translate('Video conversion failed. You may be over the site upload limit.  Try %1$suploading%2$s a smaller file, or delete some files to free up space.', '<a href="'.$this->url(array('action' => 'create', 'type'=>3)).'">', '</a>'); ?>

            </span>
          </div>
        <?php endif;?>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php else:?>
    <div class="tip">
     <span>

      <?php echo $this->translate('You do not have any videos.');?>
      <?php if ($this->can_create): ?>
        <?php echo $this->translate('Get started by %1$sposting%2$s a new video.', '<a href="'.$this->url(array('action' => 'create')).'">', '</a>'); ?>
      <?php endif; ?>
      </span>
    </div>

  <?php endif; ?>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
