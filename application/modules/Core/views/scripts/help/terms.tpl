<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: terms.tpl 8406 2011-02-07 22:16:28Z steve $
 * @author     John
 */
?>

<h2><?php echo $this->translate('Terms of Service') ?></h2>
<p>
  <?php 
  $str = $this->translate('_CORE_TERMS_OF_SERVICE');
  if ($str == strip_tags($str)) {
    // there is no HTML tags in the text
    echo nl2br($str);
  } else {
    echo $str;
  }
  ?>
</p>