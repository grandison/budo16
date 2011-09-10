<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: upload.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>
<div>
  <?php echo $this->htmlLink(array('action' => 'index', 'reset' => false), $this->translate('Back to File Manager')) ?>
</div>

<br />

<div class="error">
  <?php echo $this->error ?>
</div>