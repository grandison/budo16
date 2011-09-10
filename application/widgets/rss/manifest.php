<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Rss
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
return array(
  'package' => array(
    'type' => 'widget',
    'name' => 'rss',
    'version' => '4.0.3',
    'revision' => '$Revision: 8448 $',
    'path' => 'application/widgets/rss',
    'repository' => 'socialengine.net',
    'title' => 'RSS Feed',
    'description' => 'Displays an RSS feed.',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.0.3' => array(
        'Controller.php' => 'Fixed issue with getting link',
        'index.tpl' => 'Fixed issue with getting link; added link enabling; added option to not strip HTML',
        'manifest.php' => 'Incremented version; added option to not strip HTML',
      ),
      '4.0.2' => array(
        'index.tpl' => 'Added styles',
        'manifest.php' => 'Incremented version',
      ),
    ),
    'directories' => array(
      'application/widgets/rss',
    ),
  ),

  // Backwards compatibility
  'type' => 'widget',
  'name' => 'rss',
  'version' => '4.0.2',
  'revision' => '$Revision: 8448 $',
  'title' => 'RSS',
  'description' => 'Displays an RSS feed.',
  'category' => 'Widgets',
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
        'Text',
        'url',
        array(
          'label' => 'URL'
        )
      ),
      array(
        'Radio',
        'strip',
        array(
          'label' => 'Strip HTML?',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
          ),
          'value' => 1,
        )
      ),
    ),
  ),
) ?>