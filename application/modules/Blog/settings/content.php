<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Blogs',
    'description' => 'Displays a member\'s blog entries on their profile.',
    'category' => 'Blogs',
    'type' => 'widget',
    'name' => 'blog.profile-blogs',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Blogs',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Popular Blog Entries',
    'description' => 'Displays a list of most viewed blog entries.',
    'category' => 'Blogs',
    'type' => 'widget',
    'name' => 'blog.list-popular-blogs',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Blog Entries',
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
    'title' => 'Recent Blog Entries',
    'description' => 'Displays a list of recently posted blog entries.',
    'category' => 'Blogs',
    'type' => 'widget',
    'name' => 'blog.list-recent-blogs',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Blog Entries',
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