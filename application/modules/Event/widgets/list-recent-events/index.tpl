<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8450 2011-02-12 01:21:35Z john $
 * @author     John
 */
?>

<ul class="generic_list_widget">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class="photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.icon'), array('class' => 'thumb')) ?>
      </div>
      <div class="info">
        <div class="title">
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
        </div>
        <div class="stats">
          <?php echo $this->timestamp(strtotime($item->starttime)) ?>
          <?php //echo $this->timestamp(strtotime($item->{$this->recentCol})) ?>
          - <?php echo $this->translate('hosted by %1$s',
              $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle())) ?>
        </div>
        <div class="description">
          <?php echo $this->string()->truncate($this->string()->stripTags($item->description), 300) ?>
        </div>
      </div>
      <?php
        $desc = trim($this->string()->truncate($this->string()->stripTags($item->description), 300));
        if( !empty($desc) ): ?>
        <div class="description">
          <?php echo $desc ?>
        </div>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
