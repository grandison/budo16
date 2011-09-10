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
    'title' => 'Profile Forum Topics',
    'description' => 'Displays a member\'s forum topics on their profile.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.profile-forum-topics',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Forum Topics',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Profile Forum Posts',
    'description' => 'Displays a member\'s forum posts on their profile.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.profile-forum-posts',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Forum Posts',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Recent Forum Topics',
    'description' => 'Displays recently created forum topics.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.list-recent-topics',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Forum Topics',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
  array(
    'title' => 'Recent Forum Posts',
    'description' => 'Displays recent forum posts.',
    'category' => 'Forum',
    'type' => 'widget',
    'name' => 'forum.list-recent-posts',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Forum Posts',
    ),
    'requirements' => array(
      'no-subject',
    ),
  ),
) ?>