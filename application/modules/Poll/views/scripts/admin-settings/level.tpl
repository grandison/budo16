<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: level.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>

<h2><?php echo $this->translate("Polls Plugin") ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">
//<![CDATA[
$('level_id').addEvent('change', function(){
  window.location.href = en4.core.baseUrl + 'admin/poll/settings/level/id/'+this.get('value');
});
//]]>
</script>