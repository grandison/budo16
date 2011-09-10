<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 8941 2011-05-14 02:18:46Z jung $
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

<h3>
  <?php if( '' != ($title = trim($this->conversation->getTitle())) ): ?>
    <?php echo $title ?>
  <?php else: ?>
    <em>
      <?php echo $this->translate('(No Subject)') ?>
    </em>
  <?php endif; ?>
</h3>

<div class="message_view_header">
  <div class="message_view_between">
    <?php
      // Resource
      if( $this->resource ) {
        echo $this->translate('To members of %1$s', $this->resource->toString());
      }
      // Recipients
      else {
        $you  = array_shift($this->recipients);
        $you  = $this->htmlLink($you->getHref(), ($you==$this->viewer()?$this->translate('You'):$you->getTitle()));
        $them = array();
        foreach ($this->recipients as $r) {
          if ($r != $this->viewer()) {
              $them[] = ($r==$this->blocker?"<s>":"").$this->htmlLink($r->getHref(), $r->getTitle()).($r==$this->blocker?"</s>":"");
          } else {
              $them[] = $this->htmlLink($r->getHref(), $this->translate('You'));
          }
        }

        if (count($them)) echo $this->translate('Between %1$s and %2$s', $you, $this->fluentList($them));
        else echo 'Conversation with a deleted member.';
      }
    ?>
  </div>
  <div class="message_view_actions">
    <?php echo $this->htmlLink(array(
      'action' => 'delete',
      'id' => null,
      'message_ids' => $this->conversation->conversation_id,
    ), $this->translate('Delete'), array(
      'class' => 'buttonlink smoothbox', //'buttonlink icon_message_delete',
    )) ?>
  </div>
</div>

<ul class="message_view">
  <?php foreach( $this->messages as $message ):
    $user = $this->user($message->user_id); ?>
    <li>
      <div class='message_view_leftwrapper'>
        <div class='message_view_photo'>
          <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
        </div>
        <div class='message_view_from'>
          <p>
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
          </p>
          <p class="message_view_date">
            <?php echo $this->timestamp($message->date) ?>
          </p>
        </div>
      </div>
      <div class='message_view_info'>
        <?php echo nl2br(html_entity_decode($message->body)) ?>
        <?php if( !empty($message->attachment_type) && null !== ($attachment = $this->item($message->attachment_type, $message->attachment_id))): ?>
          <div class="message_attachment">
            <?php if(null != ( $richContent = $attachment->getRichContent(false, array('message'=>$message->conversation_id)))): ?>
              <?php echo $richContent; ?>
            <?php else: ?>
              <div class="message_attachment_photo">
                <?php if( null !== $attachment->getPhotoUrl() ): ?>
                  <?php echo $this->itemPhoto($attachment, 'thumb.normal') ?>
                <?php endif; ?>
              </div>
              <div class="message_attachment_info">
                <div class="message_attachment_title">
                  <?php echo $this->htmlLink($attachment->getHref(array('message'=>$message->conversation_id)), $attachment->getTitle()) ?>
                </div>
                <div class="message_attachment_desc">
                  <?php echo $attachment->getDescription() ?>
                </div>
              </div>
           <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>

  <?php if( !$this->locked ): ?>
    <li class='message_quick_entry'>
      <div class='message_view_leftwrapper'>
        <div class='message_view_photo'>
          &nbsp;
        </div>
        <div class='message_view_from'>
          <p>
            &nbsp;
          </p>
          <p class="message_view_date">
            &nbsp;
          </p>
        </div>
      </div>

      <div class='message_view_info'>
      <?php if( (!$this->blocked && !$this->viewer_blocked) || (count($this->recipients)>1)): ?>
        <?php echo $this->form->setAttrib('id', 'messages_form_reply')->render($this) ?>
      <?php elseif ($this->viewer_blocked):?>
        <?php echo $this->translate('You can no longer respond to this message because you have blocked %1$s.', $this->viewer_blocker->getTitle())?>
      <?php else:?>
        <?php echo $this->translate('You can no longer respond to this message because %1$s has blocked you.', $this->blocker->getTitle())?>
      <?php endif; ?>
      </div>
  <?php endif ?>

  </li>
</ul>


<?php if( !$this->locked ): ?>

  <?php
      $this->headScript()
        ->appendFile($this->baseUrl() . '/externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
        ->appendFile($this->baseUrl().'/application/modules/Core/externals/scripts/composer.js');
  ?>

  <script type="text/javascript">
  //<![CDATA[
    var composeInstance;
    en4.core.runonce.add(function() {
      var tel = new Element('div', {
        'id' : 'compose-tray',
        'styles' : {
          'display' : 'none'
        }
      }).inject($('submit'), 'before');

      var mel = new Element('div', {
        'id' : 'compose-menu'
      }).inject($('submit'), 'after');

      // @todo integrate this into the composer
      if( !Browser.Engine.presto && !Browser.Engine.trident && !DetectMobileQuick() && !DetectIpad() ) {
        composeInstance = new Composer('body', {
          overText : false,
          menuElement : mel,
          trayElement: tel,
          baseHref : '<?php echo $this->baseUrl() ?>',
          hideSubmitOnBlur : false,
          allowEmptyWithAttachment : false,
          submitElement: 'submit',
          type: 'message'
        });
      }
    });
  //]]>>
  </script>
  <?php foreach( $this->composePartials as $partial ): ?>
    <?php echo $this->partial($partial[0], $partial[1]) ?>
  <?php endforeach; ?>

<?php endif ?>