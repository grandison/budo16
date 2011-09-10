<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8479 2011-02-16 04:06:01Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Albums',
    'description' => 'Displays a member\'s albums on their profile.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.profile-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Albums',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Popular Albums',
    'description' => 'Display a list of the most popular albums.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-popular-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Albums',
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
              'comment' => 'Comments',
            ),
            'value' => 'comment',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Popular Photos',
    'description' => 'Display a list of the most popular photos.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-popular-photos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Photos',
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
              'comment' => 'Comments',
            ),
            'value' => 'comment',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Albums',
    'description' => 'Display a list of the most recent albums.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-recent-albums',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Albums',
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
    'title' => 'Recent Photos',
    'description' => 'Display a list of the most recent photos.',
    'category' => 'Albums',
    'type' => 'widget',
    'name' => 'album.list-recent-photos',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Photos',
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