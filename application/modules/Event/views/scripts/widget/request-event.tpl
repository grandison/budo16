<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: request-event.tpl 8221 2011-01-15 00:24:02Z john $
 * @author	   John
 */
?>
<script type="text/javascript">
  var eventWidgetRequestSend = function(action, event_id, notification_id, rsvp)
  {
    var url;
    if( action == 'accept' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'accept'), 'event_extended', true) ?>';
    }
    else if( action == 'reject' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'reject'), 'event_extended', true) ?>';
    }
    else
    {
      return false;
    }

    (new Request.JSON({
      'url' : url,
      'data' : {
        'event_id' : event_id,
        'format' : 'json',
        'rsvp' : rsvp
        //'token' : '<?php //echo $this->token() ?>'
      },
      'onSuccess' : function(responseJSON)
      {
        if( !responseJSON.status )
        {
          $('event-widget-request-' + notification_id).innerHTML = responseJSON.error;
        }
        else
        {
          $('event-widget-request-' + notification_id).innerHTML = responseJSON.message;
        }
      }
    })).send();
  }
</script>

<li id="event-widget-request-<?php echo $this->notification->notification_id ?>">
  <?php echo $this->itemPhoto($this->notification->getObject(), 'thumb.icon') ?>
  <div>
    <div>
      <?php echo $this->translate('%1$s has invited you to the event %2$s', $this->htmlLink($this->notification->getSubject()->getHref(), $this->notification->getSubject()->getTitle()), $this->htmlLink($this->notification->getObject()->getHref(), $this->notification->getObject()->getTitle())); ?>
    </div>
    <div>
      <button type="submit" onclick='eventWidgetRequestSend("accept", <?php echo $this->notification->getObject()->getIdentity() ?>, <?php echo $this->notification->notification_id ?>, 2)'>
        <?php echo $this->translate('Attending');?>
      </button>
      <button type="submit" onclick='eventWidgetRequestSend("accept", <?php echo $this->notification->getObject()->getIdentity() ?>, <?php echo $this->notification->notification_id ?>, 1)'>
        <?php echo $this->translate('Maybe Attending');?>
      </button>
      <?php echo $this->translate('or');?>
      <a href="javascript:void(0);" onclick='eventWidgetRequestSend("reject", <?php echo $this->notification->getObject()->getIdentity() ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('ignore request');?>
      </a>
    </div>
  </div>
</li>