<?php
/**
 * SocialEngine
 *
 * @category   Application_Theme
 * @package    Digita
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8879 2011-04-13 19:36:39Z jung $
 * @author     Bryan
 */

return array(
  'package' => array(
    'type' => 'theme',
    'name' => 'digita',
    'version' => '4.1.4',
    'revision' => '$Revision: 8879 $',
    'path' => 'application/themes/digita',
    'repository' => 'socialengine.net',
    'title' => 'Digita',
    'thumb' => 'digita.jpg',
    'author' => 'Webligo Developments',
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
      'remove',
    ),
    'callback' => array(
      'class' => 'Engine_Package_Installer_Theme',
    ),
    'directories' => array(
      'application/themes/digita',
    ),
  ),
  'files' => array(
    'theme.css',
    'constants.css',
  ),
) ?>