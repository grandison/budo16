<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 8164 2011-01-07 01:50:57Z steve $
 * @author     John
 */
?>

<h2>
<?php echo $this->htmlLink(array('route'=>'forum_general'), $this->translate("Forums"));?>
  &#187; <?php echo $this->translate($this->forum->getTitle()) ?>
</h2>

<div class="forum_header">
  <?php if( $this->canPost ): ?>
    <div class="forum_header_options">
      <?php echo $this->htmlLink($this->forum->getHref(array(
        'action' => 'topic-create',
      )), $this->translate('Post New Topic'), array(
        'class' => 'buttonlink icon_forum_post_new'
      )) ?>
    </div>
  <?php endif; ?>
  <?php if( count($this->paginator) > 0 ): ?>
    <div class="forum_header_pages">
      <?php echo $this->paginationControl($this->paginator);?>
    </div>
  <?php endif; ?>
  <div class="forum_header_moderators">
    <?php echo $this->translate('Moderators:');?>
    <?php echo $this->fluentList($this->moderators) ?>
  </div>
</div>

<?php if( count($this->paginator) > 0 ): ?>
  <ul class="forum_topics">
    <?php foreach( $this->paginator as $i => $topic ):
      $last_post = $topic->getLastCreatedPost();
      if( $last_post ) {
        $last_user = $this->user($last_post->user_id);
      } else {
        $last_user = $this->user($topic->user_id);
      }
      ?>
      <li class="forum_nth_<?php echo $i % 2 ?>">
        <div class="forum_topics_icon">
          <?php if( $topic->isViewed($this->viewer()) ): ?>
            <?php echo $this->htmlLink($topic->getHref(), $this->htmlImage('application/modules/Forum/externals/images/topic.png')) ?>
          <?php else: ?>
            <?php echo $this->htmlLink($topic->getHref(), $this->htmlImage('application/modules/Forum/externals/images/topic_unread.png')) ?>
          <?php endif; ?>
          </div>
            <div class="forum_topics_lastpost">
              <?php if( $last_post):
                list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
                ?>
                <?php echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon')) ?>
                <span class="forum_topics_lastpost_info">
                  <?php echo $this->translate(
                    '%1$sLast post%2$s by %3$s',
                    $openTag,
                    $closeTag,
                    $this->htmlLink($last_user->getHref(), $last_user->getTitle())
                  )?>
                  <?php echo $this->timestamp($topic->modified_date, array('class' => 'forum_topics_lastpost_date')) ?>
                </span>
              <?php endif; ?>
          </div>
        <div class="forum_topics_views">
          <span>
            <?php echo $this->translate(array('%1$s %2$s view', '%1$s %2$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count), '</span><span>') ?>
          </span>
        </div>
      <div class="forum_topics_replies">
        <span>
          <?php echo $this->translate(array('%1$s %2$s reply', '%1$s %2$s replies', $topic->post_count-1), $this->locale()->toNumber($topic->post_count-1), '</span><span>') ?>
        </span>
      </div>
      <div class="forum_topics_title">
        <h3<?php if( $topic->closed ): ?> class="closed"<?php endif; ?><?php if( $topic->sticky ): ?> class="sticky"<?php endif; ?>>
          <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle());?>
        </h3>
        <?php echo $this->pageLinks($topic, $this->forum_topic_pagelength, null, 'forum_pagelinks') ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="forum_pages">
  <?php echo $this->paginationControl($this->paginator);?>
</div>