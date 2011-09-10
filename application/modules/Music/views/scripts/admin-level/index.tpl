<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7576 2010-10-06 21:43:14Z john $
 * @author     Steve
 */
?>

<h2><?php echo $this->translate("Music Plugin") ?></h2>

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
  $('level_id').addEvent('change', function() {
    var url = '<?php echo $this->url(array('id' => null)) ?>';
    window.location.href = url + '/index/id/' + this.get('value');
  });
  //]]>
</script>