<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7301 2010-09-06 23:13:40Z john $
 * @author     Sami
 */
?>

<h3>
  <?php echo $this->translate('Event Details') ?>
</h3>
<div id='event_stats'>
  <ul>
    <?php if (!empty($this->subject()->description)):?>
    <li>
      <?php echo nl2br($this->subject()->description);?>
    </li>
    <?php endif ?>
    <li class="event_date">
      <?php
        // Convert the dates for the viewer
        $startDateObject = new Zend_Date(strtotime($this->subject->starttime));
        $endDateObject = new Zend_Date(strtotime($this->subject->endtime));
        if( $this->viewer() && $this->viewer()->getIdentity() ) {
          $tz = $this->viewer()->timezone;
          $startDateObject->setTimezone($tz);
          $endDateObject->setTimezone($tz);
        }
      ?>
      <?php if( $this->subject->starttime == $this->subject->endtime ): ?>
        <div class="label">
          <?php echo $this->translate('Date') ?>
        </div>
        <div class="event_stats_content">
          <?php echo $this->locale()->toDate($startDateObject) ?>
        </div>

        <div class="label">
          <?php echo $this->translate('Time') ?>
        </div>
        <div class="event_stats_content">
          <?php echo $this->locale()->toTime($startDateObject) ?>
        </div>

      <?php elseif( $startDateObject->toString('y-MM-dd') == $endDateObject->toString('y-MM-dd') ): ?>
        <div class="label">
          <?php echo $this->translate('Date')?>
        </div>
        <div class="event_stats_content">
          <?php echo $this->locale()->toDate($startDateObject) ?>
        </div>

        <div class="label">
          <?php echo $this->translate('Time')?>
        </div>
        <div class="event_stats_content">
          <?php echo $this->locale()->toTime($startDateObject) ?>
          -
          <?php echo $this->locale()->toTime($endDateObject) ?>
        </div>

      <?php else: ?>  
        <div class="event_stats_content">
          <?php echo $this->translate('%1$s at %2$s',
            $this->locale()->toDate($startDateObject),
            $this->locale()->toTime($startDateObject)
          ) ?>
          - <br />
          <?php echo $this->translate('%1$s at %2$s',
            $this->locale()->toDate($endDateObject),
            $this->locale()->toTime($endDateObject)
          ) ?>
        </div>
      <?php endif ?>
    </li>
    
    <?php if (!empty($this->subject()->location)):?>
    <li>
      <div class="label"><?php echo $this->translate('Where')?></div>
      <div class="event_stats_content"><?php echo $this->subject()->location; ?> <?php echo $this->htmlLink('http://maps.google.com/?q='.urlencode($this->subject()->location), $this->translate('Map'), array('target' => 'blank')) ?></div>
    </li>
    <?php endif;?>
    
    <?php if (!empty($this->subject()->host)):?>
    <?php if ($this->subject()->host != $this->subject()->getParent()->getTitle()):?>
    <li>
      <div class="label"><?php echo $this->translate('Host');?></div>
      <div class="event_stats_content"><?php echo $this->subject()->host; ?></div>
    </li>
   <?php endif;?>
    <li>
      <div class="label"><?php echo $this->translate('Led by');?></div>
      <div class="event_stats_content"><?php echo $this->subject()->getParent()->__toString(); ?></div>
    </li>
    <?php endif;?>
  
    <li class="event_stats_info">
      <div class="label"><?php echo $this->translate('RSVPs');?></div>
      <div class="event_stats_content">
        <ul>
          <li>
            <?php echo $this->locale()->toNumber($this->subject()->getAttendingCount()) ?>
            <span><?php echo $this->translate('attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject()->getMaybeCount()) ?>
            <span><?php echo $this->translate('maybe attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject()->getNotAttendingCount()) ?>
            <span><?php echo $this->translate('not attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject()->getAwaitingReplyCount()) ?>
            <span><?php echo $this->translate('awaiting reply');?></span>
          </li>
        </ul>
      </div>
    </li>
  </ul>
</div>