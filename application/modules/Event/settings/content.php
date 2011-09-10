<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 * 
 */
return array(
  array(
    'title' => 'Upcoming Events',
    'description' => 'Displays the logged-in member\'s upcoming events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.home-upcoming',
    'isPaginated' => true,
    'requirements' => array(
      'viewer',
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Text',
          'title',
          array(
            'label' => 'Title'
          )
        ),
        array(
          'Radio',
          'type',
          array(
            'label' => 'Show',
            'multiOptions' => array(
              '1' => 'Any upcoming events.',
              '2' => 'Current member\'s upcoming events.',
              '0' => 'Any upcoming events when member is logged out, that member\'s events when logged in.',
            ),
            'value' => '0',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Profile Events',
    'description' => 'Displays a member\'s events on their profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Events',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Event Profile Discussions',
    'description' => 'Displays a event\'s discussions on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-discussions',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Info',
    'description' => 'Displays a event\'s info (creation date, member count, etc) on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-info',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Members',
    'description' => 'Displays a event\'s members on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-members',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Options',
    'description' => 'Displays a menu of actions (edit, report, join, invite, etc) that can be performed on a event on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-options',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Photo',
    'description' => 'Displays a event\'s photo on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-photo',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Photos',
    'description' => 'Displays a event\'s photos on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-photos',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile RSVP',
    'description' => 'Displays options for RSVP\'ing to an event on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-rsvp',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Event Profile Status',
    'description' => 'Displays a event\'s title on it\'s profile.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.profile-status',
    'requirements' => array(
      'subject' => 'event',
    ),
  ),
  array(
    'title' => 'Popular Events',
    'description' => 'Displays a list of most viewed events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.list-popular-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Events',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Radio',
          'popularType',
          array(
            'label' => 'Popular Type',
            'multiOptions' => array(
              'view' => 'Views',
              'member' => 'Members',
            ),
            'value' => 'view',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Events',
    'description' => 'Displays a list of recently created events.',
    'category' => 'Events',
    'type' => 'widget',
    'name' => 'event.list-recent-events',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Events',
    ),
    'requirements' => array(
      'no-subject',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Radio',
          'recentType',
          array(
            'label' => 'Recent Type',
            'multiOptions' => array(
              'creation' => 'Creation Date',
              'modified' => 'Modified Date',
              'start' => 'Started',
              'end' => 'Ended',
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
) ?>