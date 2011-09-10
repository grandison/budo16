<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: browse.tpl 8330 2011-01-28 23:21:05Z john $
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
  <?php endif; ?>
</div>

<div class='layout_middle'>
  <?php if( $this->tag ): ?>
    <h3>
      <?php echo $this->translate('Videos using the tag') ?>
      #<?php echo $this->tag ?>
      <a href="<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'browse'), 'default', true) ?>">(x)</a>
    </h3>
  <?php endif; ?>

  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

  <ul class="videos_browse">
    <?php foreach( $this->paginator as $item ): ?>
      <li>
        
        <div class="video_thumb_wrapper">
          <?php if( $item->duration ): ?>
          <span class="video_length">
            <?php
              if( $item->duration > 360 ) $duration = gmdate("H:i:s", $item->duration); else $duration = gmdate("i:s", $item->duration);
              if( $duration[0] == '0' ) $duration = substr($duration,1);
              echo $duration;
            ?>
          </span>
          <?php endif ?>
          <?php
            if( $item->photo_id ) echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.normal'));
            else echo '<img alt="" src="application/modules/Video/externals/images/video.png">';
          ?>
        </div>
        <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'video_title')) ?>
        <div class="video_author">
          <?php echo $this->translate('By') ?>
          <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>
        </div>
        <div class="video_stats">
          <span class="video_views">
            <?php echo $this->translate(array('%1$s view', '%1$s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>
          </span>
          <?php if( $item->rating > 0 ): ?>
            <?php for( $x=1; $x<=$item->rating; $x++ ): ?>
              <span class="rating_star_generic rating_star"></span>
            <?php endfor; ?>
            <?php if( (round($item->rating) - $item->rating) > 0): ?>
              <span class="rating_star_generic rating_star_half"></span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php elseif( $this->category ):?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has posted a video with that criteria.');?>
      </span>
    </div>
  <?php else:?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has created a video yet.');?>
        <?php if ($this->can_create):?>
          <?php echo $this->translate('Be the first to %1$spost%2$s one!', '<a href="'.$this->url(array('action' => 'create'), "video_general").'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->formValues,
      'pageAsQuery' => true,
    )); ?>
</div>
