<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8371 2011-02-01 09:49:11Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Profile Classifieds',
    'description' => 'Displays a member\'s classifieds on their profile.',
    'category' => 'Classifieds',
    'type' => 'widget',
    'name' => 'classified.profile-classifieds',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Classifieds',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject' => 'user',
    ),
  ),
  array(
    'title' => 'Popular Classifieds',
    'description' => 'Displays a list of most viewed classifieds.',
    'category' => 'Classifieds',
    'type' => 'widget',
    'name' => 'classified.list-popular-classifieds',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Popular Classifieds',
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
            'value' => 'view',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Recent Classifieds',
    'description' => 'Displays a list of recently posted classifieds.',
    'category' => 'Classifieds',
    'type' => 'widget',
    'name' => 'classified.list-recent-classifieds',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Classifieds',
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