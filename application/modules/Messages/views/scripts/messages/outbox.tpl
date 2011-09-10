<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: outbox.tpl 8562 2011-03-04 01:52:49Z jung $
 * @author     John
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('My Messages');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<?php if( count($this->paginator) ): ?>
  <div class="messages_list">
    <ul>
      <?php foreach( $this->paginator as $conversation ):
        $message = $conversation->getOutboxMessage($this->viewer());
        $recipient = $conversation->getRecipientInfo($this->viewer());
        $resource = "";
        if( $conversation->hasResource() &&
                  ($resource = $conversation->getResource()) ) {
          $sender = $resource;
        } else if( $conversation->recipients > 1 ) {
          $sender = $this->viewer();
        } else {
          foreach( $conversation->getRecipients() as $tmpUser ) {
            if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
              $sender = $tmpUser;
              break;
            }
          }
        }
        if( !isset($sender) || !$sender ) {
          $sender = $this->viewer();
        }
        ?>
        <li<?php if( !$recipient->inbox_read ): ?> class='messages_list_new'<?php endif; ?> id="message_conversation_<?php echo $conversation->getIdentity() ?>">
          <div class="messages_list_checkbox">
            <input class="checkbox" type="checkbox" value="<?php echo $conversation->getIdentity() ?>" />
          </div>
          <div class="messages_list_photo">
            <?php echo $this->htmlLink($sender->getHref(), $this->itemPhoto($sender, 'thumb.icon')) ?>
          </div>
          <div class="messages_list_from">
            <p class="messages_list_from_name">
              <?php if( !empty($resource) ): ?>
                <?php echo $resource->toString() ?>
              <?php elseif( $conversation->recipients == 1 ): ?>
                <?php echo $this->htmlLink($sender->getHref(), $sender->getTitle()) ?>
              <?php else: ?>
                <?php echo $this->translate(array('%s person', '%s people', $conversation->recipients),
                    $this->locale()->toNumber($conversation->recipients)) ?>
              <?php endif; ?>
            </p>
            <p class="messages_list_from_date">
              <?php echo $this->timestamp($message->date) ?>
            </p>
          </div>
          <div class="messages_list_info">
            <p class="messages_list_info_title">
              <?php
                ( '' != ($title = trim($message->getTitle())) ||
                  '' != ($title = trim($conversation->getTitle())) ||
                  $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
              ?>
              <?php echo $this->htmlLink($conversation->getHref(), $title) ?>
            </p>
            <p class="messages_list_info_body">
              <?php echo html_entity_decode($message->body) ?>
            </p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <br />
  <button id="delete"><?php echo $this->translate('Delete Selected');?></button>
  <script type="text/javascript">
  <!--
  $('delete').addEvent('click', function(){
    var selected_ids = new Array();
    $$('div.messages_list input[type=checkbox]').each(function(cBox) {
      if (cBox.checked)
        selected_ids[ selected_ids.length ] = cBox.value;
    });
    var sb_url = '<?php echo $this->url(array(), 'messages_delete') ?>/'+selected_ids.join(',');
    if (selected_ids.length > 0)
      Smoothbox.open(sb_url);
  });
  //-->
  </script>
  <br />
  <br />

<?php else: ?>
  <p><?php echo $this->translate(array('You have %s sent message total', 'You have %s sent messages total', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?></p>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate('Tip: %1$sClick here%2$s to send your first message!', "<a href='".$this->url(array('action' => 'compose'), 'messages_general')."'>", '</a>'); ?>
    </span>
  </div>
<?php endif; ?>

<?php echo $this->paginationControl($this->paginator); ?>
