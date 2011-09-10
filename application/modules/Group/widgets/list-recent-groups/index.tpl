<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8427 2011-02-09 23:11:24Z john $
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
          <h3>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
          </h3>
        </div>
        <div class="stats">
          <?php echo $this->timestamp(strtotime($item->{$this->recentCol})) ?>
          - <?php echo $this->translate('led by %1$s',
              $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle())) ?>
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
