<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Rss
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8448 2011-02-12 01:08:24Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    $$('.rss_desc').enableLinks();
  });
</script>

<?php if( !empty($this->channel) ): ?>
  <ul>
    <?php $count=0;foreach( $this->channel['items'] as $item ): $count++ ?>
      <li class="rss_item">
        <div class="rss_item_<?php echo $count ?>">
          <?php echo $this->htmlLink($item['guid'], $item['title'],
              array('target' => '_blank', 'class' => 'rss_link_' . $count)) ?>
          <p class="rss_desc">
            <?php if( $this->strip ): ?>
              <?php echo $this->string()->truncate($this->string()->stripTags($item['description']), 350) ?>
            <?php else: ?>
              <?php echo $item['description'] ?>
            <?php endif ?>
          </p>
        </div>
        <div class="rss_time">
          <?php echo $this->locale()->toDateTime(strtotime($item['pubDate']), array('size' => 'long')) ?>
        </div>
      </li>
    <?php endforeach; ?>
    <li class="rss_last_row">
      <div>
        &nbsp;
      </div>
      <div>
        &#187; <?php echo $this->htmlLink($this->channel['link'], $this->translate("More")) ?>
      </div>
    </li>
  </ul>
<?php endif; ?>