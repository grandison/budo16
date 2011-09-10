<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: browse.tpl 7994 2010-12-08 21:14:21Z char $
 * @author     Sami
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Events');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<div class='layout_right'>
  <?php echo $this->formFilter->setAttrib('class', 'filters')->render($this) ?>

  <?php if( count($this->quickNavigation) > 0 ): ?>
    <div class="quicklinks">
      <?php
        // Render the menu
        echo $this->navigation()
          ->menu()
          ->setContainer($this->quickNavigation)
          ->render();
      ?>
    </div>
  <?php endif; ?>
</div>

<div class='layout_middle'>
  <?php if( count($this->paginator) > 0 ): ?>
    <ul class='events_browse'>
      <?php foreach( $this->paginator as $event ): ?>
        <li>
          <div class="events_photo">
            <?php echo $this->htmlLink($event->getHref(), $this->itemPhoto($event, 'thumb.normal')) ?>
          </div>
          <div class="events_options">
          </div>
          <div class="events_info">
            <div class="events_title">
              <h3><?php echo $this->htmlLink($event->getHref(), $event->getTitle()) ?></h3>
            </div>
	    <div class="events_members">
	      <?php echo $this->locale()->toDateTime($event->starttime) ?>
	    </div>
            <div class="events_members">
              <?php echo $this->translate(array('%s guest', '%s guests', $event->membership()->getMemberCount()),$this->locale()->toNumber($event->membership()->getMemberCount())) ?>
              <?php echo $this->translate('led by') ?>
              <?php echo $this->htmlLink($event->getOwner()->getHref(), $event->getOwner()->getTitle()) ?>
            </div>
            <div class="events_desc">
              <?php echo $event->getDescription() ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'query' => $this->formValues,
      )); ?>
    <?php endif; ?>

  <?php else: ?>

    <div class="tip">
      <span>
      <?php if( $this->filter != "past" ): ?>
        <?php echo $this->translate('Nobody has created an event yet.') ?>
        <?php if( $this->canCreate ): ?>
          <?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action'=>'create'), 'event_general').'">', '</a>'); ?>
        <?php endif; ?>
      <?php else: ?>
        <?php echo $this->translate('There are no past events yet.') ?>
      <?php endif; ?>
      </span>
    </div>

  <?php endif; ?>
    
</div>