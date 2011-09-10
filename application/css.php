<?php
/**
 * @package     Engine_Core
 * @version     $Id: css.php 8218 2011-01-14 23:04:59Z john $
 * @copyright   Copyright (c) 2008 Webligo Developments
 * @license     http://www.socialengine.net/license/
 */

// Config
if( !defined('_ENGINE_R_MAIN') ) {
  define('_ENGINE_R_CONF', true);
  define('_ENGINE_R_INIT', false);
  include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'index.php';
}

// Error reporting
ini_set('display_errors', TRUE);
error_reporting(E_ALL & ~E_STRICT);

/**
 * Set the server variable for document root. A lot of
 * the utility functions depend on this. Windows servers
 * don't set this, so we'll add it manually if it isn't set.
 */
if(!isset($_SERVER['DOCUMENT_ROOT']))
{
	if (isset($_SERVER['SERVER_SOFTWARE']) && 0 === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/'))
	{
	    $_SERVER['DOCUMENT_ROOT'] = rtrim(substr(
	        $_SERVER['PATH_TRANSLATED']
	        ,0
	        ,strlen($_SERVER['PATH_TRANSLATED']) - strlen($_SERVER['SCRIPT_NAME'])
	    ), '\\');
	    if ($unsetPathInfo) {
	        unset($_SERVER['PATH_INFO']);
	    }
	}
}

# Include the config file
include APPLICATION_PATH . '/application/settings/scaffold.php';

# Load the libraries. Do it manually if you don't like this way.
include APPLICATION_PATH . '/application/libraries/Scaffold/libraries/Bootstrap.php';

// Scaffold constants
define('SCAFFOLD_SYSPATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Scaffold' . DIRECTORY_SEPARATOR);
define('SCAFFOLD_DOCROOT', $config['document_root']);
define('SCAFFOLD_URLPATH', dirname(dirname($_SERVER["SCRIPT_NAME"])));

set_include_path(
  APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Scaffold' . PATH_SEPARATOR .
  get_include_path()
);

// Double check some of the config options
if( isset($config['log_path']) && !@is_dir($config['log_path']) ) {
  @mkdir($config['log_path'], 0777, true);
}
if( isset($config['cache']) && !@is_dir($config['cache']) ) {
  @mkdir($config['cache'], 0777, true);
}

/**
 * Set timezone, just in case it isn't set. PHP 5.2+
 * throws a tantrum if you try and use time() without
 * this being set.
 */
if( function_exists('date_default_timezone_set') ) {
  date_default_timezone_set('GMT');
}

// B/c
if( !isset($_GET['f']) && isset($_GET['request']) ) {
  $_GET['f'] = $_GET['request'];
}

// Process expires flush counter
$_GET['c'] = ( !isset($_GET['c']) || !is_numeric($_GET['c']) ? '0' : $_GET['c'] );

# And we're off!
if( isset($_GET['f']) ) {
	/**
	 * The files we want to parse. Full absolute URL file paths work best.
	 * eg. request=/themes/stylesheets/master.css,/themes/stylesheets/screen.css
	 */
	$files = explode(',', @$_GET['f']);

        /**
         * Remove directory traversal and null byte chars. Don't have files
         * will .. in the name or that start with 0
         */
        foreach( $files as $index => $file ) {
          $files[$index] = str_replace(array("..", "\0", "\\0"), '', $file);
        }

	/**
	 * Various options can be set in the URL. Scaffold
	 * itself doesn't use these, but they are handy hooks
	 * for modules to activate functionality if they are
	 * present.
	 */
	$options = (isset($_GET['options'])) ? array_flip(explode(',',$_GET['options'])) : array();

	/**
	 * Whether to output the CSS, or return the result of Scaffold
	 */
	$display = true;

	/**
	 * Set a base directory
	 */
	if(isset($_GET['d']))
	{
		foreach($files as $key => $file)
		{
			$files[$key] = Scaffold_Utils::join_path($_GET['d'],$file);
		}
	}

	/**
	 * Parse and join an array of files
	 */
	$result = Scaffold::parse($files,$config,$options,$display);

	if($display === false)
		stop($result);
}

/**
 * Prints out the value and exits.
 *
 * @author Anthony Short
 * @param $var
 */
function stop($var = '')
{
	if( $var == '' ) $var = 'Hammer time! Line ' . __LINE__;
	header('Content-Type: text/plain');
	print_r($var);
	exit;
}