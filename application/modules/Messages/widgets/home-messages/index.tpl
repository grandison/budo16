<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<?php if( count($this->paginator) ): ?>
  <ul>
    <?php foreach( $this->paginator as $conversation ):
      $message = $conversation->getInboxMessage($this->viewer());
      $recipient = $conversation->getRecipientInfo($this->viewer());
      if( $conversation->hasResource() &&
                ($resource = $conversation->getResource()) ) {
        $sender = $resource;
      } else if( $conversation->recipients > 1 ) {
        $sender = $this->viewer();
      } else {
        foreach( $conversation->getRecipients() as $tmpUser ) {
          if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
            $sender = $tmpUser;
          }
        }
      }
      if( !isset($sender) || !$sender ) {
        $sender = $this->viewer();
      }
      if( $resource ) {
        $author = $resource->toString();
      } else if( $conversation->recipients == 1 ) {
        $author = $this->htmlLink($sender->getHref(), $sender->getTitle());
      } else {
        $author = $this->translate(array('%s person', '%s people', $conversation->recipients),
            $this->locale()->toNumber($conversation->recipients));
      }
      ?>
      <li<?php if( !$recipient->inbox_read ): ?> class="new"<?php endif; ?>>
        <div class="from">
          <?php echo $this->translate('From %s %s', $author, $this->timestamp($message->date)) ?>
        </div>
        <p class="title">
          <?php
            ( '' != ($title = trim($message->getTitle())) ||
              '' != ($title = trim($conversation->getTitle())) ||
              $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
            $title = $this->string()->truncate($this->string()->stripTags($title));
          ?>
          <?php echo $this->htmlLink($conversation->getHref(), $title) ?>
        </p>
        <p class="body">
          <?php echo $this->string()->truncate($this->string()->stripTags(str_replace('&nbsp;', ' ', html_entity_decode($message->body)))) ?>
        </p>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
