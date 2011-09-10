<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7381 2010-09-14 21:00:44Z john $
 * @author		 John
 */
?>
<?php if( $this->canPost || $this->paginator->count() > 1 ): ?>
  <div>
    <?php if( $this->canPost ): ?>
      <?php echo $this->htmlLink(array(
        'route' => 'group_extended',
        'controller' => 'topic',
        'action' => 'create',
        'subject' => $this->subject()->getGuid(),
      ), $this->translate('Post New Topic'), array(
        'class' => 'buttonlink icon_group_photo_new'
      )) ?>
    <?php endif;?>
    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->htmlLink(array(
        'route' => 'group_extended',
        'controller' => 'topic',
        'action' => 'index',
        'subject' => $this->subject()->getGuid(),
      ), $this->translate('View All ').$this->paginator->getTotalItemCount().' Topics', array(
        'class' => 'buttonlink icon_viewmore'
      )) ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="group_discussions_list">
    <ul class="group_discussions">
      <?php foreach( $this->paginator as $topic ):
        $lastpost = $topic->getLastPost();
        $lastposter = $topic->getLastPoster();
        ?>
        <li>
          <div class="group_discussions_replies">
            <span>
              <?php echo $this->locale()->toNumber($topic->post_count - 1) ?>
            </span>
            <?php echo $this->translate(array('reply', 'replies', $topic->post_count - 1)) ?>
          </div>
          <div class="group_discussions_lastreply">
            <?php echo $this->htmlLink($lastposter->getHref(), $this->itemPhoto($lastposter, 'thumb.icon')) ?>
            <div class="group_discussions_lastreply_info">
              <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> <?php echo $this->translate('by');?> <?php echo $lastposter->__toString() ?>
              <br />
              <?php echo $this->timestamp(strtotime($topic->modified_date), array('tag' => 'div', 'class' => 'group_discussions_lastreply_info_date')) ?>
            </div>
          </div>
          <div class="group_discussions_info">
            <h3<?php if( $topic->sticky ): ?> class='group_discussions_sticky'<?php endif; ?>>
              <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
            </h3>
            <div class="group_discussions_blurb">
              <?php echo $this->viewMore(strip_tags($topic->getDescription())) ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

<?php else: ?>

  <?php if( $this->viewer()->getIdentity() ) echo '<br />'; ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('No topics have been posted in this group yet.');?>
    </span>
  </div>

<?php endif; ?>