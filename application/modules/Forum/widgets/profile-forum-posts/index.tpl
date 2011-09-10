<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     Char
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = $('forum_topic_posts').getParent();
    $('forum_topic_posts_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('forum_topic_posts_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('forum_topic_posts_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('forum_topic_posts_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>

<?php $user = $this->subject; ?>

<ul class="forum_topic_posts" id="forum_topic_posts">
  <?php foreach( $this->paginator as $post ):
    if( !isset($signature) ) $signature = $post->getSignature();
    $topic = $post->getParent();
    $forum = $topic->getParent();
    ?>
    <li>
      <div class="forum_topic_posts_info">
        <div class="forum_topic_posts_info_top">
          <div class="forum_topic_posts_info_top_date">
            <?php echo $this->locale()->toDateTime(strtotime($post->creation_date));?>
          </div>
          <div class="forum_topic_posts_info_top_parents">
            <?php echo $this->translate('in the topic %1$s', $topic->__toString()) ?>
            <?php echo $this->translate('in the forum %1$s', $forum->__toString()) ?>
          </div>
        </div>
        <div class="forum_topic_posts_info_body">
          <?php if( $this->decode_bbcode ) {
            echo nl2br($this->BBCode($post->body));
          } else {
            echo $post->body;
          } ?>
          <?php if( $post->edit_id ): ?>
            <i>
              <?php echo $this->translate('This post was edited by %1$s at %2$s', $this->user($post->edit_id)->__toString(), $this->locale()->toDateTime(strtotime($post->creation_date))); ?>
            </i>
          <?php endif;?>
        </div>
        <?php if( $post->file_id ): ?>
          <div class="forum_topic_posts_info_photo">
            <?php echo $this->itemPhoto($post, null, '', array('class'=>'forum_post_photo'));?>
          </div>
        <?php endif;?>
      </div>
    </li>
  <?php endforeach;?>
</ul>

<div>
  <div id="forum_topic_posts_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="forum_topic_posts_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
