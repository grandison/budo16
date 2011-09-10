<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: settings.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>


<script type="text/javascript">
  window.addEvent('domready', function(){
    var smtp = $$('#mail_smtp_server-wrapper, \
                          #mail_smtp_port-wrapper, \
                          #mail_smtp_authentication-wrapper, \
                          #mail_smtp_username-wrapper, \
                          #mail_smtp_password-wrapper, \
                          #mail_smtp_ssl-wrapper');

    var auth = $$('#mail_smtp_username-wrapper, #mail_smtp_password-wrapper');

    if( ($$('input[id=mail_smtp-1]:checked').length) ){
      smtp.setStyle('display','block');
    } else {
      smtp.setStyle('display','none');
    }

    $$('input[name=mail_smtp]').addEvent('change', function(){
      if( !this.checked ) return;
      if( this.value == 1 ){
        smtp.setStyle('display','block');
        if( ($$('input[id=mail_smtp_authentication-1]:checked').length) ){
          auth.setStyle('display','block');
        } else {
          auth.setStyle('display','none');
        }
      } else {
        smtp.setStyle('display','none');
      }
    });

    if(  ($$('input[id=mail_smtp-1]:checked').length) &&
      ($$('input[id=mail_smtp_authentication-1]:checked').length) ){
      auth.setStyle('display','block');
    } else {
      auth.setStyle('display','none');
    }

    $$('input[name=mail_smtp_authentication]').addEvent('change', function(){
      if( !this.checked ) return;
      if( this.value == 1 ){
        auth.setStyle('display','block');
      } else {
        auth.setStyle('display','none');
      }
    });

  });

</script>
