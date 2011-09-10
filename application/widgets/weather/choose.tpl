<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Weather
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: choose.tpl 8273 2011-01-19 04:41:46Z char $
 * @author     John
 */
?>

<form action="<?php echo $this->url() ?>" method="post">
  <input type="text" name="location" />
  <br />
  <button type="submit">Submit</button>
</form>



<?php if( !empty($this->locations) ): ?>

  <ul>
    <?php foreach( $this->locations as $location ): ?>
      <li>
        <?php echo $this->htmlLink($this->url(array('location' => $location->name)), $location->name) ?>
      </li>
    <?php endforeach; ?>
  </ul>

<?php endif; ?>



<?php if( $this->resolved ): ?>

  <script type="text/javascript">
    window.onload = function() {
      parent.window.location.replace( parent.window.location.href );
    }
  </script>

<?php endif; ?>