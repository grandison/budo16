<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8405 2011-02-05 03:36:15Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Videos',
    'description' => 'Displays a member\'s videos on their profile.',
    'category' => 'Videos',
    'type' => 'widget',
    'name' => 'video.profile-videos',
    'isPaginated' => true,
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Recent Videos',
    'description' => 'Displays a list of recently uploaded videos.',
    'category' => 'Videos',
    'type' => 'widget',
    'name' => 'video.list-recent-videos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Videos',
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
  array(
    'title' => 'Popular Videos',
    'description' => 'Displays a list of most viewed videos.',
    'category' => 'Videos',
    'type' => 'widget',
    'name' => 'video.list-popular-videos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Videos',
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
              'rating' => 'Rating',
              'view' => 'Views',
              'comment' => 'Comments',
            ),
            'value' => 'view',
          )
        ),
      )
    ),
  ),
//  array(
//    'title' => 'People Also Liked',
//    'description' => 'Displays a list of other videos that the people who liked this video also liked.',
//    'category' => 'Videos',
//    'type' => 'widget',
//    'name' => 'video.show-also-liked',
//    'isPaginated' => true,
//    'defaultParams' => array(
//      'title' => 'People Also Liked',
//    ),
//    'requirements' => array(
//      'subject' => 'video',
//    ),
//  ),
//  array(
//    'title' => 'Other Videos From Member',
//    'description' => 'Displays a list of other videos that the member that uploaded this video uploaded.',
//    'category' => 'Videos',
//    'type' => 'widget',
//    'name' => 'video.show-same-poster',
//    'isPaginated' => true,
//    'defaultParams' => array(
//      'title' => 'From the same Member',
//    ),
//    'requirements' => array(
//      'subject' => 'video',
//    ),
//  ),
//  array(
//    'title' => 'Similar Videos',
//    'description' => 'Displays a list of other videos that are similar to the current video, based on tags.',
//    'category' => 'Videos',
//    'type' => 'widget',
//    'name' => 'video.show-same-tags',
//    'isPaginated' => true,
//    'defaultParams' => array(
//      'title' => 'Similar Videos',
//    ),
//    'requirements' => array(
//      'subject' => 'video',
//    ),
//  ),
) ?>