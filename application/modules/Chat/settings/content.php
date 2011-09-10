<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: content.php 8427 2011-02-09 23:11:24Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Chat Box',
    'description' => 'Displays the chat box.',
    'category' => 'Chat',
    'type' => 'widget',
    'name' => 'chat.chat',
    'defaultParams' => array(
      'title' => 'Chat',
    ),
    'requirements' => array(
      'viewer',
      'no-subject',
    ),
  ),
) ?>