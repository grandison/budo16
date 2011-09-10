<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: delete.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */
?>

  <form method="post" class="global_form_popup">
    <div>
      <h3><?php echo $this->translate("Delete Album Category?") ?></h3>
      <p>
        <?php echo $this->translate("Are you sure that you want to delete this category? It will not be recoverable after being deleted.") ?>
      </p>
      <br />
      <p>
        <input type="hidden" name="confirm" value="<?php echo $this->album_id?>"/>
        <button type='submit'><?php echo $this->translate("Delete") ?></button>
        or <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>cancel</a>
      </p>
    </div>
  </form>

<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
