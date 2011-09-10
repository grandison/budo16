<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8070 2010-12-16 22:14:29Z char $
 * @access	   John
 */
?>

<a id="event_profile_members_anchor"></a>

<script type="text/javascript">
  var eventMemberSearch = '<?php echo $this->search ?>';
  var eventMemberPage = Number('<?php echo $this->members->getCurrentPageNumber() ?>');
  var waiting = '<?php echo $this->waiting ?>';
  en4.core.runonce.add(function() {
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    $('event_members_search_input').addEvent('keypress', function(e) {
      if( e.key != 'enter' ) return;

      en4.core.request.send(new Request.HTML({
        'url' : url,
        'data' : {
          'format' : 'html',
          'subject' : en4.core.subject.guid,
          'search' : this.value
        }
      }), {
        'element' : $('event_profile_members_anchor').getParent()
      });
    });
  });

  var paginateEventMembers = function(page) {
    //var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action' => 'profile-members', 'subject' => $this->subject()->getGuid(), 'format' => 'html'), 'default', true) ?>';
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    en4.core.request.send(new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'subject' : en4.core.subject.guid,
        'search' : eventMemberSearch,
        'page' : page,
        'waiting' : waiting
      }
    }), {
      'element' : $('event_profile_members_anchor').getParent()
    });
  }
</script>

<?php if( !empty($this->waitingMembers) && $this->waitingMembers->getTotalItemCount() > 0 ): ?>
<script type="text/javascript">
  var showWaitingMembers = function() {
    //var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action' => 'profile-members', 'subject' => $this->subject()->getGuid(), 'format' => 'html'), 'default', true) ?>';
    var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
    en4.core.request.send(new Request.HTML({
      'url' : url,
      'data' : {
        'format'  : 'html',
        'subject' : en4.core.subject.guid,
        'waiting' : true
      }
    }), {
      'element' : $('event_profile_members_anchor').getParent()
    });
  }
</script>
<?php endif; ?>

<?php if( !$this->waiting ): ?>
  <div class="event_members_info">
    <div class="event_members_search">
      <input id="event_members_search_input" type="text" value="<?php echo $this->translate('Search Guests');?>" onfocus="$(this).store('over', this.value);this.value = '';" onblur="this.value = $(this).retrieve('over');">
    </div>
    <div class="event_members_total">
      <?php if( '' == $this->search ): ?>
        <?php echo $this->translate(array('This event has %1$s guest.', 'This event has %1$s guests.', $this->members->getTotalItemCount()),$this->locale()->toNumber($this->members->getTotalItemCount())) ?>
      <?php else: ?>
        <?php echo $this->translate(array('This event has %1$s guest that matched the query "%2$s".', 'This event has %1$s guests that matched the query "%2$s".', $this->members->getTotalItemCount()), $this->locale()->toNumber($this->members->getTotalItemCount()), $this->search) ?>
      <?php endif; ?>
    </div>
    <?php if( !empty($this->waitingMembers) && $this->waitingMembers->getTotalItemCount() > 0 ): ?>
      <div class="event_members_total">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('See Waiting'), array('onclick' => 'showWaitingMembers(); return false;')) ?>
      </div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="event_members_info">
    <div class="event_members_total">
      <?php echo $this->translate(array('This event has %s member waiting approval or waiting for a invite response.', 'This event has %s members waiting approval or waiting for a invite response.', $this->members->getTotalItemCount()),$this->locale()->toNumber($this->members->getTotalItemCount())) ?>
    </div>
  </div>
<?php endif; ?>

<?php if( $this->members->getTotalItemCount() > 0 ): ?>
  <ul class='event_members'>
    <?php foreach( $this->members as $member ):
      if( !empty($member->resource_id) ) {
        $memberInfo = $member;
        $member = $this->item('user', $memberInfo->user_id);
      } else {
        $memberInfo = $this->event->membership()->getMemberInfo($member);
      }
      ?>

      <li id="event_member_<?php echo $member->getIdentity() ?>">

        <?php echo $this->htmlLink($member->getHref(), $this->itemPhoto($member, 'thumb.icon'), array('class' => 'event_members_icon')) ?>
        <div class='event_members_options'>

          <?php // Remove/Promote/Demote member ?>
          <?php if( $this->event->isOwner($this->viewer())): ?>

            <?php if( !$this->event->isOwner($member) && $memberInfo->active == true ): ?>
              <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Remove Member'), array(
                'class' => 'buttonlink smoothbox icon_friend_remove'
              )) ?>
            <?php endif; ?>
            <?php if( $memberInfo->active == false && $memberInfo->resource_approved == false ): ?>
              <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'approve', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Approve Request'), array(
                'class' => 'buttonlink smoothbox icon_event_accept'
              )) ?>
              <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Reject Request'), array(
                'class' => 'buttonlink smoothbox icon_event_reject'
              )) ?>
            <?php endif; ?>
            <?php if( $memberInfo->active == false && $memberInfo->resource_approved == true ): ?>
              <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'member', 'action' => 'cancel', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Cancel Invite'), array(
                'class' => 'buttonlink smoothbox icon_event_cancel'
              )) ?>
            <?php endif; ?>


          <?php endif; ?>
        </div>
        <div class='event_members_body'>
          <div>
            <span class='event_members_status'>
              <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>

              <?php // Titles ?>
              <?php if( $this->event->getParent()->getGuid() == ($member->getGuid())): ?>
                (<?php echo ( $memberInfo->title ? $memberInfo->title : 'owner' ) ?>)
              <?php endif; ?>

            </span>
            <span>
              <?php echo $member->status; ?>
            </span>
          </div>
          <div class="event_members_rsvp">
            <?php if( $memberInfo->rsvp == 0 ): ?>
              <?php echo $this->translate('Not Attending') ?>
            <?php elseif( $memberInfo->rsvp == 1 ): ?>
              <?php echo $this->translate('Maybe Attending') ?>
            <?php elseif( $memberInfo->rsvp == 2 ): ?>
              <?php echo $this->translate('Attending') ?>
            <?php else: ?>
              <?php echo $this->translate('Awaiting Reply') ?>
            <?php endif; ?>
          </div>
        </div>

      </li>

    <?php endforeach;?>

  </ul>


  <?php if( $this->members->count() > 1 ): ?>
    <div>
      <?php if( $this->members->getCurrentPageNumber() > 1 ): ?>
        <div id="user_event_members_previous" class="paginator_previous">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
            'onclick' => 'paginateEventMembers(eventMemberPage - 1)',
            'class' => 'buttonlink icon_previous',
            'style' => '',
          )); ?>
        </div>
      <?php endif; ?>
      <?php if( $this->members->getCurrentPageNumber() < $this->members->count() ): ?>
        <div id="user_event_members_next" class="paginator_next">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
            'onclick' => 'paginateEventMembers(eventMemberPage + 1)',
            'class' => 'buttonlink icon_next'
          )); ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>