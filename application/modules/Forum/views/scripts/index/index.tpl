<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7481 2010-09-27 08:41:01Z john $
 * @author     John
 */
?>

<h2>
  <?php echo $this->translate('Forums') ?>
</h2>

<ul class="forum_categories">

<?php foreach( $this->categories as $category ):
  if( empty($this->forums[$category->category_id]) ) {
    continue;
  }
  ?>
  <li>
    <div>
      <h3><?php echo $this->translate($category->getTitle()) ?></h3>
    </div>
    <ul>
      <?php foreach( $this->forums[$category->category_id] as $forum ):
        $last_topic = $forum->getLastUpdatedTopic();
        $last_post = null;
        $last_user = null;
        if( $last_topic ) {
          $last_post = $last_topic->getLastCreatedPost();
          $last_user = $this->user($last_post->user_id);
        }
        ?>
        <li>
          <div class="forum_icon">
            <?php echo $this->htmlLink($forum->getHref(), $this->htmlImage('application/modules/Forum/externals/images/forum.png')) ?>
          </div>
          <div class="forum_lastpost">
          <?php if( $last_topic && $last_post ): ?>
            <?php echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon')) ?>
            <span class="forum_lastpost_info">
              <?php echo $this->translate(
                'Last reply by %1$s in %2$s',
                $last_user->__toString(),
                $this->htmlLink($last_post->getHref(), $last_topic->getTitle())
              ) ?>
              <?php echo $this->timestamp($last_post->creation_date, array('class' => 'forum_lastpost_date')) ?>
            </span>
          <?php endif;?>
          </div>
          <div class="forum_posts">
            <span><?php echo $forum->post_count;?></span>
            <span>
              <?php echo $this->translate(array('post', 'posts', $forum->post_count),$this->locale()->toNumber($forum->post_count)) ?>
            </span>
          </div>
          <div class="forum_topics">
            <span><?php echo $forum->topic_count;?></span>
            <span>
              <?php echo $this->translate(array('topic', 'topics', $forum->topic_count),$this->locale()->toNumber($forum->topic_count)) ?>
            </span>
          </div>
          <div class="forum_title">
            <h3>
              <?php echo $this->htmlLink($forum->getHref(), $this->translate($forum->getTitle())) ?>
            </h3>
            <span>
              <?php echo $forum->getDescription() ?>
            </span>
          </div>
        </li>
      <?php endforeach;?>
      </ul>
  </li>

<?php endforeach;?>
</ul>

