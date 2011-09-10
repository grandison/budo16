<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 8453 2011-02-12 02:09:18Z john $
 * @author		 John
 */
?>

<h2>
  <?php echo $this->group->__toString() ?>
  <?php echo $this->translate('&#187;'); ?>
  <?php echo $this->htmlLink(array(
        'route' => 'group_extended',
        'controller' => 'topic',
        'action' => 'index',
        'subject' => $this->group->getGuid(),
      ), $this->translate('Discussions')) ?>
</h2>

<br />

<h3>
  <?php echo $this->topic->getTitle() ?>
</h3>

<?php $this->placeholder('grouptopicnavi')->captureStart(); ?>
<div class="group_discussions_thread_options">
  <?php echo $this->htmlLink(array('route' => 'group_extended', 'controller' => 'topic', 'action' => 'index', 'group_id' => $this->group->getIdentity()), $this->translate('Back to Topics'), array(
    'class' => 'buttonlink icon_back'
  )) ?>
  <?php if( $this->canPost && !$this->topic->closed): ?>
    <?php echo $this->htmlLink($this->url(array()) . '#reply', $this->translate('Post Reply'), array(
      'class' => 'buttonlink icon_group_post_reply'
    )) ?>
  <?php endif; ?>
  <?php if( $this->viewer->getIdentity() ): ?>
    <?php if( !$this->isWatching ): ?>
      <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array(
        'class' => 'buttonlink icon_group_topic_watch'
      )) ?>
    <?php else: ?>
      <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array(
        'class' => 'buttonlink icon_group_topic_unwatch'
      )) ?>
    <?php endif; ?>
  <?php endif; ?>
  <?php if( $this->canEdit ): ?>
    <?php if( !$this->topic->sticky ): ?>
      <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array(
        'class' => 'buttonlink icon_group_post_stick'
      )) ?>
    <?php else: ?>
      <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array(
        'class' => 'buttonlink icon_group_post_unstick'
      )) ?>
    <?php endif; ?>
    <?php if( !$this->topic->closed ): ?>
      <?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array(
        'class' => 'buttonlink icon_group_post_close'
      )) ?>
    <?php else: ?>
      <?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array(
        'class' => 'buttonlink icon_group_post_open'
      )) ?>
    <?php endif; ?>
    <?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array(
      'class' => 'buttonlink smoothbox icon_group_post_rename'
    )) ?>
    <?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array(
      'class' => 'buttonlink smoothbox icon_group_post_delete'
    )) ?>
  <?php elseif( $this->group->isOwner($this->viewer()) == false): ?>
    <?php if( $this->topic->closed ): ?>
      <div class="group_discussions_thread_options_closed">
        <?php echo $this->translate('This topic has been closed.');?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php $this->placeholder('grouptopicnavi')->captureEnd(); ?>



<?php echo $this->placeholder('grouptopicnavi') ?>
<?php echo $this->paginationControl(null, null, null, array(
  'params' => array(
    'post_id' => null // Remove post id
  )
)) ?>


<script type="text/javascript">
  var quotePost = function(user, href, body) {
    if( $type(body) == 'element' ) {
      body = $(body).getParent('li').getElement('.group_discussions_thread_body_raw').get('html').trim();
    }
    $("body").value = '[blockquote]' + '[b][url=' + href + ']' + user + '[/url] said:[/b]\n' + htmlspecialchars_decode(body) + '[/blockquote]\n\n';
    $("body").focus();
    $("body").scrollTo(0, $("body").getScrollSize().y);
  }
  en4.core.runonce.add(function() {
    $$('.group_discussions_thread_body').enableLinks();
  });
</script>



<ul class='group_discussions_thread'>
  <?php foreach( $this->paginator as $post ):
    $user = $this->item('user', $post->user_id);
    $isOwner = false;
    $isOfficer = false;
    $isMember = false;
    $liClass = 'group_discussions_thread_author_none';
    if( $this->group->isOwner($user) ) {
      $isOwner = true;
      $isMember = true;
      $liClass = 'group_discussions_thread_author_isowner';
    } else if( ($officerInfo = $this->officerList->get($user)) ) {
      $isOfficer = true;
      $isMember = true;
      $liClass = 'group_discussions_thread_author_isofficer';
    } else if( $this->group->membership()->isMember($user) ) {
      $isMember = true;
      $liClass = 'group_discussions_thread_author_ismember';
    }
    ?>
  <li class="<?php echo $liClass ?>">
    <div class="group_discussions_thread_author">
      <div class="group_discussions_thread_author_name">
        <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
      </div>
      <div class="group_discussions_thread_photo">
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
      </div>
      <div class="group_discussions_thread_author_rank">
        <?php
          if( $isOwner ) {
            echo $this->translate('Leader');
          } else if( $isOfficer ) {
            //if( empty($officerInfo->title) ) {
              echo $this->translate('Officer');
            //} else {
            //  echo $officerInfo->title;
            //}
          } else if( $isMember ) {
            echo $this->translate('Member');
          }
        ?>
      </div>
    </div>
    <div class="group_discussions_thread_info">
      <div class="group_discussions_thread_details">
        <div class="group_discussions_thread_details_options">
          <?php if( $this->form ): ?>
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Quote'), array(
              'class' => 'buttonlink icon_group_post_quote',
              'onclick' => 'quotePost("'.$this->escape($user->getTitle()).'", "'.$this->escape($user->getHref()).'", this);',
            )) ?>
          <?php endif; ?>
          <?php if( $post->user_id == $this->viewer()->getIdentity() || $this->group->getOwner()->getIdentity() == $this->viewer()->getIdentity() || $this->canEdit): ?>
            <?php echo $this->htmlLink(array('route' => 'group_extended', 'controller' => 'post', 'action' => 'edit', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Edit'), array(
              'class' => 'buttonlink smoothbox icon_group_post_edit'
            )) ?>
            <?php echo $this->htmlLink(array('route' => 'group_extended', 'controller' => 'post', 'action' => 'delete', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array(
              'class' => 'buttonlink smoothbox icon_group_post_delete'
            )) ?>
          <?php endif; ?>
        </div>
        <div class="group_discussions_thread_details_date">
          <?php echo $this->translate('Posted') ?>
          <?php echo $this->timestamp(strtotime($post->creation_date)) ?>
        </div>
      </div>
      <div class="group_discussions_thread_body">
        <?php echo nl2br($this->BBCode($post->body, array('link_no_preparse' => true))) ?>
      </div>
      <span class="group_discussions_thread_body_raw" style="display: none;">
        <?php echo $post->body; ?>
      </span>
    </div>
  </li>
  <?php endforeach; ?>
</ul>


<?php if($this->paginator->getCurrentItemCount() > 4): ?>

  <?php echo $this->paginationControl(null, null, null, array(
    'params' => array(
      'post_id' => null // Remove post id
    )
  )) ?>
  <br />
  <?php echo $this->placeholder('grouptopicnavi') ?>

<?php endif; ?>

<br />

<?php if( $this->form ): ?>
  <a name="reply" />
  <?php echo $this->form->setAttrib('id', 'group_topic_reply')->render($this) ?>
<?php endif; ?>