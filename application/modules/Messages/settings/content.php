<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8822 2011-04-09 00:30:46Z john $
 * @author     John Boehr <j@webligo.com>
 */
return array(
  array(
    'title' => 'Recent Messages',
    'description' => 'Displays a list of the signed in user\'s recent messages.',
    'category' => 'Messages',
    'type' => 'widget',
    'name' => 'messages.home-messages',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Messages',
    ),
    'requirements' => array(
      'viewer',
      'no-subject',
    ),
  ),
) ?>