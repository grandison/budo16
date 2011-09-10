<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
?>

<?php if( $this->canChat ): ?>

  <?php
    $this->headScript()
      ->prependFile($this->baseUrl() . '/externals/soundmanager/script/soundmanager2'
           . (APPLICATION_ENV == 'production' ? '-nodebug-jsmin' : '' ) . '.js')
      ->appendFile('application/modules/Chat/externals/scripts/core.js');
    $this->headTranslate(array(
      'The chat room has been disabled by the site admin.', 'Browse Chatrooms',
      'You are sending messages too quickly - please wait a few seconds and try again.',
      '%1$s has joined the room.', '%1$s has left the room.', 'Settings',
      'Friends Online', 'None of your friends are online.', 'Go Offline',
      'Open Chat', 'General Chat', 'Introduce Yourself', '%1$s person',
    ));
  ?>
  
  <script type="text/javascript">
    
    en4.core.runonce.add(function() {
        if( !$type(window._chatHandler) ) {
          chatHandler = new ChatHandler({
            'baseUrl' : en4.core.baseUrl,
            'basePath' : en4.core.basePath,
            //'identity' : <?php echo sprintf('%d', $this->viewer()->getIdentity()) ?>,
            'enableIM' : <?php echo $this->canIM ? 'true' : 'false' ?>,
            'enableChat' : <?php echo $this->canChat ? 'true' : 'false' ?>,
            'delay' : <?php echo sprintf('%d', Engine_Api::_()->getApi('settings', 'core')->getSetting('chat.general.delay', '5000')); ?>,
            'chatOptions' : {
              'operator' : <?php echo sprintf('%d', (int) $this->isOperator) ?>,
              'roomList' : <?php echo Zend_Json::encode($this->rooms) ?>,
              'container' : <?php echo ( $this->chatContainer ? "'" . $this->chatContainer . "'" : 'null' ) ?>
            }
          });
          chatHandler.start();
          window._chatHandler = chatHandler;
        }
        if( $type(window._chatHandler) ) {
          window._chatHandler.startChat({
            operator : <?php echo sprintf('%d', (int) $this->isOperator) ?>,
            roomList : <?php echo Zend_Json::encode($this->rooms) ?>
          });
        }
    });

  </script>

<?php else: ?>

  <div><?php echo $this->translate('The chat room has been disabled by the site admin.')?></div>
  
<?php endif; ?>

  