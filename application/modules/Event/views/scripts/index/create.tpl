<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: create.tpl 8280 2011-01-20 21:21:50Z jung $
 * @author     Sami
 */
?>

<?php if( $this->parent_type == 'group' ) { ?>
  <h2>
    <?php echo $this->group->__toString() ?>
    <?php echo $this->translate('&#187; Events');?>
  </h2>
<?php } else { ?>
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
<?php } ?>

<?php echo $this->form->render() ?>

<script type="text/javascript">
  var cal_starttime_onHideStart = function(){
    // check end date and make it the same date if it's too
    cal_endtime.calendars[0].start = new Date( $('starttime-date').value );
    // redraw calendar
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', 1);
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', -1);
  }
  var cal_endtime_onHideStart = function(){
    // check start date and make it the same date if it's too
    cal_starttime.calendars[0].end = new Date( $('endtime-date').value );
    // redraw calendar
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', 1);
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', -1);
  }
</script>