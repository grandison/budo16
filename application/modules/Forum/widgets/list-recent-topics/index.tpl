<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<ul>
  <?php foreach( $this->paginator as $topic ):
    $user = $topic->getOwner('user');
    $forum = $topic->getParent();
    ?>
    <li>
      <?php /*
      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('class' => 'thumb')) ?>
       *
       */ ?>
      <div class='info'>
        <div class='name'>
          <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
        </div>
        <div class='author'>
          <?php echo $this->translate('By') ?>
          <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
        </div>
        <div class="parent">
          <?php echo $this->translate('In') ?>
          <?php echo $this->htmlLink($forum->getHref(), $forum->getTitle()) ?>
        </div>
        <div class='date'>
          <?php echo $this->timestamp($topic->creation_date) ?>
        </div>
      </div>
      <div class='description'>
        <?php echo $this->viewMore(strip_tags($topic->getDescription()), 64) ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>