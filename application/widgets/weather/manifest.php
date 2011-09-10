<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Weather
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
return array(
  'package' => array(
    'type' => 'widget',
    'name' => 'weather',
    'version' => '4.0.1',
    'revision' => '$Revision: 8273 $',
    'path' => 'application/widgets/weather',
    'repository' => 'socialengine.net',
    'title' => 'Weather',
    'description' => 'Displays the weather.',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.0.1' => array(
        'choose.tpl' => 'Page now reloads when location is selected',
      )
    ),
    'directories' => array(
      'application/widgets/weather',
    ),
  ),

  // Backwards compatibility
  'type' => 'widget',
  'name' => 'weather',
  'version' => '4.0.1',
  'revision' => '$Revision: 8273 $',
  'title' => 'Weather',
  'description' => 'Displays the weather.',
  'category' => 'Widgets',
) ?>