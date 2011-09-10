<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _activityText.tpl 8933 2011-05-12 20:37:57Z jung $
 * @author     Jung
 */
?>

<?php if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
   $actions = $this->actions;
} ?>

<?php $this->headScript()->appendFile('application/modules/Activity/externals/scripts/core.js')
        ->appendFile($this->baseUrl() . '/externals/flowplayer/flashembed-1.0.1.pack.js');?>

<script type="text/javascript">
  var CommentLikesTooltips;
  en4.core.runonce.add(function() {
    // Add hover event to get likes
    $$('.comments_comment_likes').addEvent('mouseover', function(event) {
      var el = $(event.target);
      if( !el.retrieve('tip-loaded', false) ) {
        el.store('tip-loaded', true);
        el.store('tip:title', '<?php echo  $this->string()->escapeJavascript($this->translate('Loading...')) ?>');
        el.store('tip:text', '');
        var id = el.get('id').match(/\d+/)[0];
        // Load the likes
        var url = '<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'get-likes'), 'default', true) ?>';
        var req = new Request.JSON({
          url : url,
          data : {
            format : 'json',
            //type : 'core_comment',
            action_id : el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
            comment_id : id
          },
          onComplete : function(responseJSON) {
            el.store('tip:title', responseJSON.body);
            el.store('tip:text', '');
            CommentLikesTooltips.elementEnter(event, el); // Force it to update the text
          }
        });
        req.send();
      }
    });
    // Add tooltips
    CommentLikesTooltips = new Tips($$('.comments_comment_likes'), {
      fixed : true,
      className : 'comments_comment_likes_tips',
      offset : {
        'x' : 48,
        'y' : 16
      }
    });
  });
</script>

<ul class='feed' id="activity-feed">
  
<?php
  foreach( $actions as $action ): // (goes to the end of the file)
    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
      if( !$action->getTypeInfo()->enabled ) continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      
      ob_start();
    ?>
  <?php if( !$this->noList ): ?><li id="activity-item-<?php echo $action->action_id ?>"><?php endif; ?>
    <?php $this->commentForm->setActionIdentity($action->action_id) ?>
    <script type="text/javascript">
      (function(){
        var action_id = '<?php echo $action->action_id ?>';
        en4.core.runonce.add(function(){
          $('activity-comment-body-' + action_id).autogrow();
          en4.activity.attachComment($('activity-comment-form-' + action_id));
        });
      })();
    </script>

    <?php // User's profile photo ?>
    <div class='feed_item_photo'><?php echo $this->htmlLink($action->getSubject()->getHref(),
      $this->itemPhoto($action->getSubject(), 'thumb.icon', $action->getSubject()->getTitle())
    ) ?></div>


    <div class='feed_item_body'>
      
      <?php // Main Content ?>
      <span class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
        <?php echo $action->getContent() ?>
      </span>

      <?php // Attachments ?>
      <?php if( $action->getTypeInfo()->attachable && $action->attachment_count > 0 ): // Attachments ?>
        <div class='feed_item_attachments'>
          <?php if( $action->attachment_count > 0 && count($action->getAttachments()) > 0 ): ?>
            <?php if( count($action->getAttachments()) == 1 &&
                    null != ( $richContent = current($action->getAttachments())->item->getRichContent()) ): ?>
              <?php echo $richContent; ?>
            <?php else: ?>
              <?php foreach( $action->getAttachments() as $attachment ): ?>
                <span class='feed_attachment_<?php echo $attachment->meta->type ?>'>
                <?php if( $attachment->meta->mode == 0 ): // Silence ?>
                <?php elseif( $attachment->meta->mode == 1 ): // Thumb/text/title type actions ?>
                  <div>
                    <?php if( $attachment->item->getPhotoUrl() ): ?>
                      <?php 
                        if ($attachment->item->getType() == "core_link")
                        {
                          $attribs = Array('target'=>'_blank');
                        }
                        else
                        {
                          $attribs = Array();
                        } 
                      ?>
                      <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle()), $attribs) ?>
                    <?php endif; ?>
                    <div>
                      <div class='feed_item_link_title'>
                        <?php
                          if ($attachment->item->getType() == "core_link")
                          {
                            $attribs = Array('target'=>'_blank');
                          }
                          else
                          {
                            $attribs = Array();
                          }
                          echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs);
                        ?>
                      </div>
                      <div class='feed_item_link_desc'>
                        <?php echo $this->viewMore($attachment->item->getDescription()) ?>
                      </div>
                    </div>
                  </div>
                <?php elseif( $attachment->meta->mode == 2 ): // Thumb only type actions ?>
                  <div class="feed_attachment_photo">
                    <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle()), array('class' => 'feed_item_thumb')) ?>
                  </div>
                <?php elseif( $attachment->meta->mode == 3 ): // Description only type actions ?>
                  <?php echo $this->viewMore($attachment->item->getDescription()); ?>
                <?php elseif( $attachment->meta->mode == 4 ): // Multi collectible thingy (@todo) ?>
                <?php endif; ?>
                </span>
              <?php endforeach; ?>
              <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php // Icon, time since, action links ?>
      <?php
        $icon_type = 'activity_icon_'.$action->type;
        list($attachment) = $action->getAttachments();
        if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
          $icon_type .= ' item_icon_'.$attachment->item->getType() . ' ';
        endif;
        $canComment = ( $action->getTypeInfo()->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($action->getObject(), null, 'comment') &&
            !empty($this->commentForm) );
      ?>
      <div class='feed_item_date feed_item_icon <?php echo $icon_type ?>'>
        <ul>
          <li>
            <?php echo $this->timestamp($action->getTimeValue()) ?>
          </li>
          <?php if( $canComment ): ?>
            <?php if( $action->likes()->isLike($this->viewer()) ): ?>
              <li class="feed_item_option_unlike">
                <span>-</span>
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Unlike'), array('onclick'=>'javascript:en4.activity.unlike('.$action->action_id.');')) ?>
              </li>
            <?php else: ?>
              <li class="feed_item_option_like">
                <span>-</span>
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Like'), array('onclick'=>'javascript:en4.activity.like('.$action->action_id.');')) ?>
              </li>
            <?php endif; ?>
            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
              <li class="feed_item_option_comment">
                <span>-</span>
                <?php echo $this->htmlLink(array('route'=>'default','module'=>'activity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'), $this->translate('Comment'), array(
                  'class'=>'smoothbox',
                )) ?>
              </li>
            <?php else: ?>
              <li class="feed_item_option_comment">
                <span>-</span>
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Comment'), array('onclick'=>'document.getElementById("'.$this->commentForm->getAttrib('id').'").style.display = ""; document.getElementById("'.$this->commentForm->submit->getAttrib('id').'").style.display = "block"; document.getElementById("'.$this->commentForm->body->getAttrib('id').'").focus();')) ?>
              </li>
            <?php endif; ?>
          <?php endif; ?>
          <?php if( $this->viewer()->getIdentity() && (
                $this->activity_moderate || (
                  $this->allow_delete && (
                    ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                    ('user' == $action->object_type && $this->viewer()->getIdentity()  == $action->object_id)
                  )
                )
            ) ): ?>
            <li class="feed_item_option_delete">
              <span>-</span>
              <?php echo $this->htmlLink(array(
                'route' => 'default',
                'module' => 'activity',
                'controller' => 'index',
                'action' => 'delete',
                'action_id' => $action->action_id
              ), $this->translate('Delete'), array('class' => 'smoothbox')) ?>
            </li>
          <?php endif; ?>
          <?php // Share ?>
          <?php if( $action->getTypeInfo()->shareable && $this->viewer()->getIdentity() ): ?>
            <?php if( $action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()) ): ?>
              <li class="feed_item_option_share">
                <span>-</span>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 2 ): ?>
              <li class="feed_item_option_share">
                <span>-</span>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $subject->getType(), 'id' => $subject->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 3 ): ?>
              <li class="feed_item_option_share">
                <span>-</span>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $object->getType(), 'id' => $object->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 4 ): ?>
              <li class="feed_item_option_share">
                <span>-</span>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php endif; ?>
          <?php endif; ?>
        </ul>
      </div>

      <?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
        <div class='comments'>
          <ul>
            <?php if( $action->likes()->getLikeCount() > 0 && (count($action->likes()->getAllLikesUsers())>0) ): ?>
              <li>
                <div></div>
                <div class="comments_likes">
                  <?php if( $action->likes()->getLikeCount() <= 3 || $this->viewAllLikes ): ?>
                    <?php echo $this->translate(array('%s likes this.', '%s like this.', $action->likes()->getLikeCount()), $this->fluentList($action->likes()->getAllLikesUsers()) )?>

                  <?php else: ?>
                    <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_likes' => true)),
                      $this->translate(array('%s person likes this', '%s people like this', $action->likes()->getLikeCount()), $this->locale()->toNumber($action->likes()->getLikeCount()) )
                    ) ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endif; ?>
            <?php if( $action->comments()->getCommentCount() > 0 ): ?>
              <?php if( $action->comments()->getCommentCount() > 5 && !$this->viewAllComments): ?>
                <li>
                  <div></div>
                  <div class="comments_viewall">
                    <?php if( $action->comments()->getCommentCount() > 2): ?>
                      <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_comments' => true)),
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount()))) ?>
                    <?php else: ?>
                      <?php echo $this->htmlLink('javascript:void(0);',
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount())),
                          array('onclick'=>'en4.activity.viewComments('.$action->action_id.');')) ?>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endif; ?>
              <?php foreach( $action->getComments($this->viewAllComments) as $comment ): ?>
                <li id="comment-<?php echo $comment->comment_id ?>">
                   <div class="comments_author_photo">
                      <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(),
                        $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $action->getSubject()->getTitle())
                      ) ?>
                   </div>
                   <div class="comments_info">
                     <span class='comments_author'>
                       <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle()); ?>
                     </span>
                     <?php echo $this->viewMore($comment->body) ?>
                     <ul class="comments_date">
                       <li class="comments_timestamp">
                         <?php echo $this->timestamp($comment->creation_date); ?>
                       </li>
                       <?php if ( $this->viewer()->getIdentity() &&
                                 (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                                  ($this->viewer()->getIdentity() == $comment->poster_id) ||
                                  $this->activity_moderate ) ): ?>
                       <li class="comments_delete">
                         - <?php echo $this->htmlLink(array(
                              'route'=>'default',
                              'module'    => 'activity',
                              'controller'=> 'index',
                              'action'    => 'delete',
                              'action_id' => $action->action_id,
                              'comment_id'=> $comment->comment_id,
                              ), $this->translate('delete'), array('class' => 'smoothbox')) ?>
                       </li>
                        <?php endif; ?>
                        <?php if( $canComment ):
                          $isLiked = $comment->likes()->isLike($this->viewer());
                        ?>
                          <li class="comments_like">
                            -
                            <?php if( !$isLiked ): ?>
                              <a href="javascript:void(0)" onclick="en4.activity.like(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                                <?php echo $this->translate('like') ?>
                              </a>
                            <?php else: ?>
                              <a href="javascript:void(0)" onclick="en4.activity.unlike(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                                <?php echo $this->translate('unlike') ?>
                              </a>
                            <?php endif ?>
                          </li>
                        <?php endif ?>
                        <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                          <li class="comments_likes_total">
                            -
                            <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                              <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                            </a>
                          </li>
                        <?php endif ?>
                     </ul>
                   </div>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <?php if( $canComment ) echo $this->commentForm->render() /*
          <form>
            <textarea rows='1'>Add a comment...</textarea>
            <button type='submit'>Post</button>
          </form>
          */ ?>
        </div>
      <?php endif; ?>

    </div>
  <?php if( !$this->noList ): ?></li><?php endif; ?>

<?php
      ob_end_flush();
    } catch (Exception $e) {
      ob_end_clean();
      if( APPLICATION_ENV === 'development' ) {
        echo $e->__toString();
      }
    };
  endforeach;
?>

</ul>
