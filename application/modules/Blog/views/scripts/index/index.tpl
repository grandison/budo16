<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8089 2010-12-21 01:38:39Z john $
 * @author     Jung
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Blogs');?>
  </h2>
  <?php if( count($this->navigation) > 0 ): ?>
    <div class="tabs">
      <?php
        // Render the menu
        echo $this->navigation()
          ->menu()
          ->setContainer($this->navigation)
          ->render();
      ?>
    </div>
  <?php endif; ?>
</div>

<div class='layout_right'>
  <?php echo $this->form->render($this) ?>

  <?php if( count($this->quickNavigation) > 0 ): ?>
    <div class="quicklinks">
      <?php
        // Render the menu
        echo $this->navigation()
          ->menu()
          ->setContainer($this->quickNavigation)
          ->render();
      ?>
    </div>
  <?php endif; ?>
</div>

<div class='layout_middle'>
  
  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
    <ul class="blogs_browse">
      <?php foreach( $this->paginator as $item ): ?>
        <li>
          <div class='blogs_browse_photo'>
            <?php echo $this->htmlLink($item->getOwner()->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.icon')) ?>
          </div>
          <div class='blogs_browse_info'>
            <span class='blogs_browse_info_title'>
              <h3><?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?></h3>
            </span>
            <p class='blogs_browse_info_date'>
              <?php echo $this->translate('Posted');?>
              <?php echo $this->timestamp(strtotime($item->creation_date)) ?>
              <?php echo $this->translate('by');?>
              <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>
            </p>
            <p class='blogs_browse_info_blurb'>
              <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
            </p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  
  <?php elseif( $this->category || $this->show == 2 || $this->search ):?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has written a blog entry with that criteria.');?>
        <?php if (TRUE): // @todo check if user is allowed to create a poll ?>
          <?php echo $this->translate('Be the first to %1$swrite%2$s one!', '<a href="'.$this->url(array('action' => 'create'), 'blog_general').'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>

  <?php else:?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has written a blog entry yet.'); ?>
        <?php if( $this->canCreate ): ?>
          <?php echo $this->translate('Be the first to %1$swrite%2$s one!', '<a href="'.$this->url(array('action' => 'create'), 'blog_general').'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>

  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'pageAsQuery' => true,
    'query' => $this->formValues,
    //'params' => $this->formValues,
  )); ?>

</div>