<?php

/**
 * Global Constants
 * You can set constants here that can be accessed from every CSS file.
 * This is also a good place to add any PHP environment constants
 * you might want to use.
 */
$config['global'] = array();

if( defined('APPLICATION_PATH') && file_exists(APPLICATION_PATH . '/application/settings/constants.xml') ) {
  $config['xml_path'] = APPLICATION_PATH . '/application/settings/constants.xml';
} else {
  $config['xml_path'] = false;
}