<?php

/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FormCalendarDateTime.php 8217 2011-01-14 22:58:59Z char $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_FormCalendarDateTime extends Zend_View_Helper_FormElement
{
  public function formCalendarDateTime($name, $value = null, $attribs = null,
      $options = null, $listsep = "<br />\n")
  {
    $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
    extract($info); // name, value, attribs, options, listsep, disable

    // Get date format
    if( isset($attribs['dateFormat']) ) {
      $dateFormat = $attribs['dateFormat'];
      //unset($attribs['dateFormat']);
    } else {
      $dateFormat = 'ymd';
    }

    // Get use military time
    if( isset($attribs['useMilitaryTime']) ) {
      $useMilitaryTime = $attribs['useMilitaryTime'];
      //unset($attribs['useMilitaryTime']);
    } else {
      $useMilitaryTime = true;
    }

    // Check value type
    if( is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2})(:(\d{2}))?)?$/', $value, $m) ) {
      $tmpDateFormat = trim(str_replace(array('d', 'y', 'm'), array('/%3$d', '/%1$d', '/%2$d'), $dateFormat), '/');
      $value = array();

      // Get date
      $value['date'] = sprintf($tmpDateFormat, $m[1], $m[2], $m[3]);
      if( $value['date'] == '0/0/0' ) {
        unset($value['date']);
      }

      // Get time
      if( isset($m[6]) ) {
        $value['hour'] = $m[5];
        $value['minute'] = $m[6];
        if( !$useMilitaryTime ) {
          $value['ampm'] = ( $value['hour'] >= 12 ? 'PM' : 'AM' );
          if( 0 == (int) $value['hour'] ) {
            $value['hour'] = 12;
          } else if( $value['hour'] > 12 ) {
            $value['hour'] -= 12;
          }
        }
      }
    }

    if( !is_array($value) ) {
      $value = array();
    }


    // Prepare javascript
    
    // Prepare month and day names
    $localeObject = Zend_Registry::get('Locale');
    
    $months = Zend_Locale::getTranslationList('months', $localeObject);
    if($months['default'] == NULL) { $months['default'] = "wide"; }
    $months = $months['format'][$months['default']];

    $days = Zend_Locale::getTranslationList('days', $localeObject);
    if($days['default'] == NULL) { $days['default'] = "wide"; }
    $days = $days['format'][$days['default']];

    $calendarFormatString = trim(preg_replace('/\w/', '$0/', $dateFormat), '/');
    $calendarFormatString = str_replace('y', 'Y', $calendarFormatString);
    
    // Append files and script
    $this->view->headScript()->appendFile($this->view->baseUrl() . '/externals/calendar/calendar.compat.js');
    $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/externals/calendar/styles.css');
    $this->view->headScript()->appendScript("
  en4.core.runonce.add(function() {
    window.cal_{$name} = new Calendar({ '{$name}-date': '{$calendarFormatString}' }, {
      classes: ['event_calendar'],
      pad: 0,
      direction: 0,
      months : " . Zend_Json::encode(array_values($months)) . ",
      days : " . Zend_Json::encode(array_values($days)) . ",
      day_suffixes: ['', '', '', ''],
      onHideStart: function()   { if (typeof cal_{$name}_onHideStart    == 'function') cal_{$name}_onHideStart(); },
      onHideComplete: function(){ if (typeof cal_{$name}_onHideComplete == 'function') cal_{$name}_onHideComplete(); },
      onShowStart: function()   { if (typeof cal_{$name}_onShowStart    == 'function') cal_{$name}_onShowStart(); },
      onShowComplete: function(){ if (typeof cal_{$name}_onShowComplete == 'function') cal_{$name}_onShowComplete(); }
    });
  });
");

    
    return
      '<div class="event_calendar_container" style="display:inline">' .
        $this->view->formHidden($name . '[date]', @$value['date'], array_merge(array('class' => 'calendar', 'id' => $name . '-date'), (array) @$attribs['dateAttribs'])) .
        '<span class="calendar_output_span" id="calendar_output_span_' . $name . '-date">' .
          ( @$value['date'] ? @$value['date'] : $this->view->translate('Select a date') ) .
        '</span>' .
      '</div>' .
      $this->view->formTime($name, $value, $attribs, $options)
      ;
  }
}