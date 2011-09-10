<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: job-messages.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>

<div style="padding: 10px;">
  <?php if( empty($this->messages) ): ?>

    <div>
      No messages.
    </div>

  <?php else: ?>

    <ul>
      <?php foreach( $this->messages as $message ): ?>
        <li>
          <?php echo $message ?>
        </li>
      <?php endforeach; ?>
    </ul>

  <?php endif; ?>
</div>