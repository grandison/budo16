<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: request-group.tpl 7244 2010-09-01 01:49:53Z john $
 * @author	   John
 */
?>
<script type="text/javascript">
  var groupWidgetRequestSend = function(action, group_id, notification_id)
  {
    var url;
    if( action == 'accept' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'accept'), 'group_extended', true) ?>';
    }
    else if( action == 'reject' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'reject'), 'group_extended', true) ?>';
    }
    else
    {
      return false;
    }

    (new Request.JSON({
      'url' : url,
      'data' : {
        'group_id' : group_id,
        'format' : 'json'
        //'token' : '<?php //echo $this->token() ?>'
      },
      'onSuccess' : function(responseJSON)
      {
        if( !responseJSON.status )
        {
          $('group-widget-request-' + notification_id).innerHTML = responseJSON.error;
        }
        else
        {
          $('group-widget-request-' + notification_id).innerHTML = responseJSON.message;
        }
      }
    })).send();
  }
</script>

<li id="group-widget-request-<?php echo $this->notification->notification_id ?>">
  <?php echo $this->itemPhoto($this->notification->getObject(), 'thumb.icon') ?>
  <div>
    <div>
      <?php echo $this->translate('%1$s has invited you to the group %2$s', $this->htmlLink($this->notification->getSubject()->getHref(), $this->notification->getSubject()->getTitle()), $this->htmlLink($this->notification->getObject()->getHref(), $this->notification->getObject()->getTitle())); ?>
    </div>
    <div>
      <button type="submit" onclick='groupWidgetRequestSend("accept", <?php echo $this->notification->getObject()->getIdentity() ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('Join Group');?>
      </button>
      <?php echo $this->translate('or');?>
      <a href="javascript:void(0);" onclick='groupWidgetRequestSend("reject", <?php echo $this->notification->getObject()->getIdentity() ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('ignore request');?>
      </a>
    </div>
  </div>
</li>