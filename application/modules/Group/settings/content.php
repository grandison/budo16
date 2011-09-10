<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Groups',
    'description' => 'Displays a member\'s groups on their profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-groups',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Groups',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Group Profile Discussions',
    'description' => 'Displays a group\'s discussions on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-discussions',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Info',
    'description' => 'Displays a group\'s info (creation date, member count, leader, officers, etc) on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-info',
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Members',
    'description' => 'Displays a group\'s members on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-members',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Options',
    'description' => 'Displays a menu of actions (edit, report, join, invite, etc) that can be performed on a group on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-options',
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Photo',
    'description' => 'Displays a group\'s photo on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-photo',
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Photos',
    'description' => 'Displays a group\'s photos on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-photos',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Group Profile Status',
    'description' => 'Displays a group\'s title on its profile.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-status',
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title'=> 'Group Profile Events',
    'description' => 'Displays a group\'s events on its profile',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.profile-events',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'group',
    ),
  ),
  array(
    'title' => 'Popular Groups',
    'description' => 'Displays a list of most viewed groups.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.list-popular-groups',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Groups',
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
    'title' => 'Recent Groups',
    'description' => 'Displays a list of recently created groups.',
    'category' => 'Groups',
    'type' => 'widget',
    'name' => 'group.list-recent-groups',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Groups',
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
            ),
            'value' => 'creation',
          )
        ),
      )
    ),
  ),
) ?>