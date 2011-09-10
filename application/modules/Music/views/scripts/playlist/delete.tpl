<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 7904 2010-12-03 03:36:14Z john $
 * @author     Steve
 */
?>

<div class='global_form_popup'>
  <?php if ($this->success): ?>

    <script type="text/javascript">
      var item_id = 'music_playlist_item_<?php echo $this->playlist_id ?>';
      if (parent.$(item_id))
          parent.$(item_id).destroy();
      else
          parent.location.href = '<?php echo $this->url(array(), 'music_general') ?>';
      setTimeout(function() {
        parent.Smoothbox.close();
      }, 1000 );
    </script>
    <div class="global_form_popup_message">
      <?php echo $this->translate('The selected playlist has been deleted.') ?>
    </div>

  <?php else: // success == false ?>

  <form method="POST" action="<?php echo $this->url() ?>">
    <div>
      <h3><?php echo $this->translate('Delete Playlist?') ?></h3>
      <p>
        <?php echo $this->translate('Are you sure that you want to delete the selected playlist? This action cannot be undone.') ?>
      </p>

      <p>&nbsp;</p>

      <p>
        <input type="hidden" name="playlist_id" value="<?php echo $this->playlist_id?>"/>
        <button type='submit'><?php echo $this->translate('Delete') ?></button>
        <?php echo $this->translate("or") ?> <a href="javascript:void(0);" onclick="parent.Smoothbox.close();"><?php echo $this->translate("cancel") ?></a>
      </p>
    </div>
  </form>
  <?php endif; // success ?>

</div>

<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>

