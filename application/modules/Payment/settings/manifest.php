<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8942 2011-05-16 21:40:10Z john $
 * @author     John Boehr <j@webligo.com>
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'payment',
    'version' => '4.1.5',
    'revision' => '$Revision: 8942 $',
    'path' => 'application/modules/Payment',
    'repository' => 'socialengine.net',
    'title' => 'Payment',
    'description' => 'Payment',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '4.1.3',
      ),
      array(
        'type' => 'module',
        'name' => 'user',
        'minVersion' => '4.1.3',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'class' => 'Engine_Package_Installer_Module',
    ),
    'directories' => array(
      'application/modules/Payment',
    ),
    'files' => array(
      'application/languages/en/payment.csv',
    ),
    'tests' => array(
      // PHP Extensions
      array(
        'type' => 'PhpExtension',
        'name' => 'MCrypt',
        'extension' => 'mcrypt',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'We recommend installing the mcrypt extension. ' .
              'Your payment gateway login information will be stored ' .
              'encrypted if this extension is available.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Curl',
        'extension' => 'curl',
        'messages' => array(
          'noExtension' => 'The Curl extension is required.',
        ),
      ),
      // Misc
      //array(
      //  'type' => 'PhpCurlSupport',
      //  'name' => 'Curl Features',
      //  'ssl' => true,
      //  'protocols' => array(
      //    'http',
      //    'https',
      //  ),
      //),
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onUserCreateBefore',
      'resource' => 'Payment_Plugin_Core',
    ),
    array(
      'event' => 'onUserUpdateBefore',
      'resource' => 'Payment_Plugin_Core',
    ),
    array(
      'event' => 'onAuthorizationLevelDeleteBefore',
      'resource' => 'Payment_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'payment_gateway',
    'payment_order',
    'payment_package',
    'payment_subscription',
    'payment_transaction',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    
  ),
);
