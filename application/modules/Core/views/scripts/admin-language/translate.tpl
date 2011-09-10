<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: translate.tpl 7533 2010-10-02 09:42:49Z john $
 * @author     John
 */
?>

<?php // attach a "powered by Google" branding ?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load("language", "1");
  window.addEvent('domready', function() {
    google.language.getBranding($$('.global_form')[0].getElement('h3'));//'branding');
  });
</script>
<style type="text/css">
  .gBranding {
    display:inline-block;
    padding-left: 10px;
  }
</style>

<?php if( $this->form ): ?>

  <script type="text/javascript">
    var url = '<?php echo $this->url(array('action' => 'translate-phrase')) ?>';
    var testTranslation = function() {
      (new Request.JSON({
        url : url,
        data : {
          format: 'json',
          source : $('source').value,
          target : $('target').value,
          text : $('test').value
        },
        onComplete : function(responseJSON) {
          if( $('test-translation') ) {
            $('test-translation').set('html', responseJSON.targetPhrase);
          } else {
            (new Element('p', {
              'id' : 'test-translation',
              'html' : responseJSON.targetPhrase
            })).inject($('test'), 'after');
          }

          console.log(responseJSON);
        }
      })).send();
    }
    window.addEvent('domready', function() {
      (new Element('a', {
        'href' : 'javascript:void(0);',
        'html' : 'Translate',
        'events' : {
          'click' : function() {
            testTranslation();
          }
        }
      })).inject($('test-element').getElement('p').empty());
      
    });
  </script>

  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>

<?php endif; ?>

<?php if( $this->values ): ?>

  <script type="text/javascript">
    window.addEvent('load', function() {
      
    });
  </script>

  <div id="admin_language_translate_log">
    <ul>
      
    </ul>
  </div>

<?php endif; ?>