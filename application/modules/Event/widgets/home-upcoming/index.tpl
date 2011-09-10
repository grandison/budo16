<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7543 2010-10-04 07:06:51Z john $
 * @access	   John
 */
?>

<ul id="events-upcoming">
  <?php foreach( $this->paginator as $event ):
    // Convert the dates for the viewer
    $startDateObject = new Zend_Date(strtotime($event->starttime));
    $endDateObject = new Zend_Date(strtotime($event->endtime));
    if( $this->viewer() && $this->viewer()->getIdentity() ) {
      $tz = $this->viewer()->timezone;
      $startDateObject->setTimezone($tz);
      $endDateObject->setTimezone($tz);
    }
    $isOngoing = ( $startDateObject->toValue() < time() );
    ?>
    <li<?php if( $isOngoing ):?> class="ongoing"<?php endif ?>>
      <?php echo $event->__toString() ?>
      <div class="events-upcoming-date">
        <?php echo $this->timestamp($event->starttime, array('class'=>'eventtime')) ?>
      </div>
      <?php if( $isOngoing ): ?>
        <div class="events-upcoming-ongoing">
          <?php echo $this->translate('Ongoing') ?>
        </div>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>