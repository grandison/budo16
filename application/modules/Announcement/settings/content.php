<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Announcements',
    'description' => 'Displays recent announcements.',
    'category' => 'Core',
    'type' => 'widget',
    'name' => 'announcement.list-announcements',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Announcements',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
) ?>