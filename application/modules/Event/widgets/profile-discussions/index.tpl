<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @access	   John
 */
?>

<?php if( $this->viewer()->getIdentity() || $this->paginator->count() > 1 ): ?>
  <div>
    <?php if( $this->viewer()->getIdentity() ):?>
      <?php
      echo $this->htmlLink(array(
          'route' => 'event_extended',
          'controller' => 'topic',
          'action' => 'create',
          'subject' => $this->subject()->getGuid(),
        ), $this->translate('Post New Topic'), array(
          'class' => 'buttonlink icon_event_post_new'
      ));?>
    <?php endif;?>
    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->htmlLink(array(
          'route' => 'event_extended',
          'controller' => 'topic',
          'action' => 'index',
          'subject' => $this->subject()->getGuid(),
        ), 'View All '.$this->paginator->getTotalItemCount().' Topics', array(
          'class' => 'buttonlink icon_viewmore'
      )) ?>
    <?php endif; ?>
  </div>
<?php endif;?>


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="event_discussions_list">
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
              <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> <?php echo $this->translate('by');?> <?php echo $lastposter->__toString() ?>
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
  </div>


<?php else: ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate('No topics have been posted in this event yet.');?>
    </span>
  </div>
<?php endif; ?>