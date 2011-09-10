<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */
?>

<h2>
  <?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?>
</h2>

<div class="event_discussions_options">
  <?php echo $this->htmlLink(array('route' => 'event_profile', 'id' => $this->event->getIdentity()), $this->translate('Back to Event'), array(
    'class' => 'buttonlink icon_back'
  )) ?>
  <?php if ($this->can_post) { echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'create', 'subject' => $this->event->getGuid()), $this->translate('Post New Topic'), array(
    'class' => 'buttonlink icon_event_post_new'
  )); }?>
</div>

<?php if( $this->paginator->count() > 1 ): ?>
  <div>
    <br />
    <?php echo $this->paginationControl($this->paginator) ?>
    <br />
  </div>
<?php endif; ?>

<ul class="event_discussions">
  <?php foreach( $this->paginator as $topic ):
      $lastpost = $topic->getLastPost();
      $lastposter = $topic->getLastPoster();
      ?>
    <li>
      <div class="event_discussions_replies">
        <span>
          <?php echo $this->locale()->toNumber($topic->post_count - 1) ?>
        </span>
        <?php echo $this->translate(array('reply', 'replies', $topic->post_count - 1)) ?>
      </div>
      <div class="event_discussions_lastreply">
        <?php echo $this->htmlLink($lastposter->getHref(), $this->itemPhoto($lastposter, 'thumb.icon')) ?>
        <div class="event_discussions_lastreply_info">
          <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> by <?php echo $lastposter->__toString() ?>
          <br />
          <?php echo $this->timestamp(strtotime($topic->modified_date), array('tag' => 'div', 'class' => 'event_discussions_lastreply_info_date')) ?>
        </div>
      </div>
      <div class="event_discussions_info">
        <h3<?php if( $topic->sticky ): ?> class='event_discussions_sticky'<?php endif; ?>>
          <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
        </h3>
        <div class="event_discussions_blurb">
          <?php echo $this->viewMore($topic->getDescription()) ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<?php if( $this->paginator->count() > 1 ): ?>
  <div>
    <?php echo $this->paginationControl($this->paginator) ?>
  </div>
<?php endif; ?>
