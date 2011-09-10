<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: invite.tpl 8020 2010-12-09 22:56:32Z char $
 * @author	   John
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){$('selectall').addEvent('click', function(){ $$('input[type=checkbox]').set('checked', $(this).get('checked', false)); })});
</script>

<?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>