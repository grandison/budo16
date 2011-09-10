<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: job-add.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<?php if( !$this->form ): ?>

  <?php foreach( $this->enabledJobTypes as $jobType ): ?>
    <?php echo $this->htmlLink($this->url(array('type' => $jobType->type)), $jobType->title) ?>
    <br />
  <?php endforeach; ?>

<?php else: ?>

  <div class="settings">
    <?php echo $this->form->render($this) ?>
  </div>

<?php endif; ?>