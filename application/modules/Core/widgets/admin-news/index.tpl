<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8089 2010-12-21 01:38:39Z john $
 * @author     John
 */
?>

<div class="admin_home_news">
  <h3 class="sep">
    <span><?php echo $this->translate("News & Updates") ?></span>
  </h3>

  <?php if( !empty($this->channel) ): ?>
    <ul>
      <?php foreach( $this->channel['items'] as $item ): ?>
        <li>
          <div class="admin_home_news_date">
            <?php echo $this->locale()->toDate(strtotime($item['pubDate']), array('size' => 'long')) ?>
          </div>
          <div class="admin_home_news_info">
            <a href="<?php echo $item['guid'] ?>" target="_blank">
              <?php echo $item['title'] ?>
            </a>
            <span class="admin_home_news_blurb">
              <?php echo $this->string()->truncate($this->string()->stripTags($item['description']), 350) ?>
            </span>
          </div>
        </li>
      <?php endforeach; ?>
      <li>
        <div class="admin_home_news_date">
          &nbsp;
        </div>
        <div class="admin_home_news_info">
          &#187; <a href="http://www.socialengineforum.com">Goto Socialengineforum.com ;)</a>
        </div>
      </li>
    </ul>

  <?php elseif( $this->badPhpVersion ): ?>

  <div>
    <?php echo $this->translate('The news feed requires the PHP DOM extension.') ?>
  </div>

  <?php else: ?>

  <div>
    <?php echo $this->translate('There are no news items, or we were unable to fetch the news.') ?>
  </div>

  <?php endif; ?>
</div>
