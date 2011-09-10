<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8369 2011-02-01 06:14:57Z john $
 * @author     John
 */
?>

<ul class="generic_list_widget">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class="photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.icon'), array('class' => 'thumb')) ?>
      </div>
      <div class="info">
        <div class="title">
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
        </div>
        <div class="stats">
          <?php if( $this->popularType == 'view' ): ?>
            <?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>
          <?php else /*if( $this->popularType == 'comment' )*/: ?>
            <?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>
          <?php endif; ?>
        </div>
        <div class="owner">
          <?php
            $owner = $item->getOwner();
            echo $this->translate('Posted by %1$s', $this->htmlLink($owner->getHref(), $owner->getTitle()));
          ?>
        </div>
      </div>
      <div class="description">
        <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>