<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Clock
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8292 2011-01-25 00:21:31Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  var updateClock = function() {
    var currentTime = new Date();
    currentTime.setMilliseconds(currentTime.getMilliseconds() + Date.getServerOffset());

    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

    // Choose either "AM" or "PM" as appropriate
    var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";

    // Convert the hours component to 12-hour format if needed
    currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;

    // Convert an hours component of "0" to "12"
    currentHours = ( currentHours == 0 ) ? 12 : currentHours;

    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds + " " + timeOfDay;

    //currentTimeString += ' (' + currentTime.getTimezone() + ')';

    // Update the time display
    document.getElementById("clock").firstChild.nodeValue = currentTimeString;
  }
  window.addEvent('load', function() {
    updateClock.periodical(1000);
  });
</script>

<style type="text/css">
  .layout_clock > div
  {
    text-align: center;
    padding: 15px;
    margin-bottom: 15px;
  }
  .layout_clock > div > span
  {
    display: block;
    font-size: 1.4em;
  }
</style>

<div class="quicklinks">
  <span id="clock">&nbsp;</span>
</div>
