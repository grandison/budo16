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
    var anchor = $('forum_topics').getParent();
    $('forum_topics_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('forum_topics_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('forum_topics_previous').removeEvents('click').addEvent('click', function(){
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

    $('forum_topics_next').removeEvents('click').addEvent('click', function(){
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

<ul class="forum_topics" id="forum_topics">
    <?php foreach( $this->paginator as $topic ):
      $last_post = $topic->getLastCreatedPost();
      if( $last_post ) {
        $last_user = $this->user($last_post->user_id);
      } else {
        $last_user = $this->user($topic->user_id);
      }
      ?>
      <li>
        <div class="forum_topics_icon">
          <?php if( $topic->isViewed($this->viewer()) ): ?>
            <?php echo $this->htmlLink($topic->getHref(), $this->htmlImage('application/modules/Forum/externals/images/topic.png')) ?>
          <?php else: ?>
            <?php echo $this->htmlLink($topic->getHref(), $this->htmlImage('application/modules/Forum/externals/images/topic_unread.png')) ?>
          <?php endif; ?>
          </div>
            <div class="forum_topics_lastpost">
              <?php echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon')) ?>
              <span class="forum_topics_lastpost_info">
              <?php if( $last_post):
                list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
                ?>
                <?php echo $this->translate(
                  '%1$sLast post%2$s by %3$s',
                  $openTag,
                  $closeTag,
                  $this->htmlLink($last_user->getHref(), $last_user->getTitle())
                )?>
                <?php echo $this->timestamp($topic->modified_date, array('class' => 'forum_topics_lastpost_date')) ?>
              <?php endif; ?>
            </span>
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


<div>
  <div id="forum_topics_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="forum_topics_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
