<?php
/**
 * SocialEngine
 *
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manifest.php 8820 2011-04-08 00:31:16Z john $
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'core',
    'name' => 'base',
    'version' => '4.1.4',
    'revision' => '$Revision: 8820 $',
    'path' => '/',
    'repository' => 'socialengine.net',
    'title' => 'Base',
    'description' => 'Base',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.1.4' => array(
        'index.php' => 'Added PHP version check',
        'README.html' => 'Removed duplicate step',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.1.3' => array(
        'application/comet.php' => 'Removed',
        'application/config.php' => 'Removed',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.1.2' => array(
        'application/lite.php' => 'Added cache bootstrap to prevent errors',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.1.1' => array(
        'application/.htaccess' => 'Added expires headers for static content',
        'application/css.php' => 'Fixed theoretical null-byte vulnerability',
        'application/settings/manifest.php' => 'Incremented version; added new files for settings expires headers',
        'externals/.htaccess' => 'Added expires headers for static content',
        'public/.htaccess' => 'Added expires headers for static content',
      ),
      '4.1.0' => array(
        'application/cli.php' => 'Added CLI support',
        'application/css.php' => 'Added cache and log configuration check; fixed theoretical vulnerability in file name',
        'application/index.php' => 'Fixed issue with rewrite detection; added CLI support; improved configuration loading; fixed issue that prevent logging exceptions in production mode',
        'application/offline.html' => 'Added error code support',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.0.5' => array(
        '.htaccess' => 'Added keywords',
        'index.php' => 'Added keywords',
        'README.html' => 'Added keywords',
        'xd_receiver.htm' => 'Added keywords',
        'application/config.php' => 'Added keywords',
        'application/css.php' => 'Added keywords',
        'application/index.php' => 'Added keywords',
        'application/lite.php' => 'Added keywords',
        'application/maintenance.html' => 'Added keywords',
        'application/mobile.php' => 'Added keywords',
        'application/offline.php' => 'Styled',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.0.4' => array(
        '.htaccess' => 'Fixed 500 errors on some servers',
        'robots.txt' => 'Fixed query string problems in redirect',
        'application/index.php' => 'Improved SSL support; fixed query string problems in redirect',
        'application/settings/beta1-beta2.sql' => 'Removed',
        'application/settings/beta1-beta2_classifieds.sql' => 'Removed',
        'application/settings/beta2-beta3.sql' => 'Removed',
        'application/settings/beta3-rc1.sql' => 'Removed',
        'application/settings/constants.xml' => 'Added constant theme_pulldown_contents_list_background_color_active',
        'application/settings/manifest.php' => 'Incremented version',
      ),
      '4.0.3' => array(
        '.htaccess' => 'Added better missing file handling',
        'application/css.php' => 'Removed some test code',
        'application/index.php' => 'Missing configuration files handled better',
        'application/settings/cache.php' => 'Removed',
        'application/settings/cache.sample.php' => 'Added',
        'application/settings/general.php' => 'Removed',
        'application/settings/general.sample.php' => 'Added',
        'application/settings/mail.php' => 'Removed',
        'application/settings/mail.sample.php' => 'Added',
        'application/settings/manifest.php' => 'Incremented version',
        'application/settings/session.php' => 'Removed',
        'application/settings/session.sample.php' => 'Added',
      ),
      '4.0.2' => array(
        'application/settings/manifest.php' => 'Incremented version; permissions are set in the installer',
      ),
      '4.0.1' => array(
        'index.php' => 'Added svn:keywords',
        'README.html' => 'Updated readme',
        'application/comet.php' => 'Removed',
        'application/index.php' => 'Removed comet; modification to APPLICATION_ENV handling',
        'application/settings/manifest.php' => 'Incremented version; removed comet; adding theme .htaccess to manifest files',
        'application/settings/my.sql' => 'Regenerated',
        'application/settings/session.php' => 'Default session cookie to not httponly to fix FancyUpload problems'
      ),
    ),
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
    ),
    'files' => array(
      '.htaccess',
      'README.html',
      'index.php',
      'robots.txt',
      'xd_receiver.htm',
      'application/.htaccess',
      'application/cli.php',
      'application/css.php',
      'application/index.php',
      'application/lite.php',
      'application/maintenance.html',
      'application/mobile.php',
      'application/offline.html',
      'application/libraries/index.html',
      'application/modules/index.html',
      'application/packages/index.html',
      'application/plugins/index.html',
      'application/themes/index.html',
      'application/themes/.htaccess',
      'application/widgets/index.html',
      'externals/.htaccess',
      'externals/index.html',
      'public/.htaccess',
      'public/admin/index.html',
      'public/temporary/index.html',
      'public/user/index.html',
      'temporary/.htaccess',
      'temporary/backup/index.html',
      'temporary/cache/index.html',
      'temporary/log/index.html',
      'temporary/log/scaffold/index.html',
      'temporary/package/index.html',
      'temporary/package/archives/index.html',
      'temporary/package/manifests/index.html',
      'temporary/package/packages/index.html',
      'temporary/package/repositories/index.html',
      'temporary/package/sdk/index.html',
      'temporary/scaffold/index.html',
      'temporary/session/index.html',
    ),
    'directories' => array(
      'application/settings',
    ),
    'permissions' => array(
      array(
        'path' => 'application/languages',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/packages',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/themes',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/settings',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'public',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'temporary',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
    ),
    'tests' => array(
      // PHP Version
      array(
        'type' => 'PhpVersion',
        'name' => 'PHP 5',
        'minVersion' => '5.1.2',
      ),
      // MySQL Adapters are in module-core
      // Apache Modules
      array(
        'type' => 'ApacheModule',
        'name' => 'mod_rewrite',
        'module' => 'mod_rewrite',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noModule' => 'mod_rewrite does not appear to be available. This is OK, but it might prevent you from having search engine-friendly URLs.',
        ),
      ),
      // PHP Config
      array(
        'type' => 'PhpConfig',
        'name' => 'PHP Safe Mode OFF',
        'directive' => 'safe_mode',
        'comparisonMethod' => 'l',
        'comparisonValue' => 1,
        'messages' => array(
          'badValue' => 'PHP Safe Mode is currently ON - please turn it off and try again.',
        ),
      ),
      array(
        'type' => 'PhpConfig',
        'name' => 'PHP Register Globals OFF',
        'directive' => 'register_globals',
        'comparisonMethod' => 'l',
        'comparisonValue' => 1,
        'messages' => array(
          'badValue' => 'PHP Register Globals is currently ON - please turn it off and try again.',
        ),
      ),
      // PHP Extensions
      array(
        'type' => 'PhpExtension',
        'name' => 'APC',
        'extension' => 'apc',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'For optimal performance, we recommend adding the Alternative PHP Cache (APC) extension',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'GD',
        'extension' => 'gd',
        'messages' => array(
          'noExtension' => 'The GD Image Library is required for resizing images.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Iconv',
        'extension' => 'iconv',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The Iconv library is recommended for languages other than English.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Multi-byte String',
        'extension' => 'mbstring',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The Multi-byte String (mbstring) library is required for languages other than English.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'PCRE',
        'extension' => 'pcre',
        'messages' => array(
          'noExtension' => 'The Perl-Compatible Regular Expressions extension is required.',
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
      array(
        'type' => 'PhpExtension',
        'name' => 'Session',
        'extension' => 'session',
        'messages' => array(
          'noExtension' => 'Session support is required.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'DOM',
        'extension' => 'dom',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The DOM (Document Object Model) extension is required for RSS feed parsing and link attachments.',
        ),
      ),
      // File Permissions
      array(
        'type' => 'FilePermission',
        'name' => 'Public Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'public',
        'value' => 7,
        'recursive' => true,
        'ignoreFiles' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the public/ directory',
        ),
      ),
      array(
        'type' => 'Multi',
        'name' => 'Temp Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'allForOne' => false,
        'breakOnFailure' => true,
        'messages' => array(
          'oneTestFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
          'someTestsFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
          'allTestsFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
        ),
        'tests' => array(
          array(
            'type' => 'FilePermission',
            'path' => 'temporary',
            'value' => 7,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/cache',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/log',
            'recursive' => true,
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/archives',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/packages',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/repositories',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/scaffold',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/session',
            'value' => 7,
            'ignoreMissing' => true,
          ),
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Packages Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/packages',
        'value' => 7,
        'recursive' => true,
        'ignoreFiles' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/packages/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Settings Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/settings',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/settings/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Language Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/languages',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/languages/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Theme Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/themes',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/themes/ directory',
        ),
      ),
    ),
  ),
); ?>