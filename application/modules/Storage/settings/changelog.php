<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: changelog.php 8825 2011-04-09 01:34:20Z john $
 * @author     John
 */
return array(
  '4.1.4' => array(
    'Service/S3.php' => 'Fixed issue with pulling remote file to local temporary file',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.3' => array(
    'Service/S3.php' => 'Fixed issue with pulling remote file to local temporary file',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.2p1' => array(
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.0-4.1.1.sql' => 'Fixed issue when upgrading from <= 4.1.0',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Fixed issue when upgrading from <= 4.1.0',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.1.2' => array(
    'controllers/AdminServicesController.php' => 'Added file count to services',
    'controllers/IndexController.php' => 'Added expires headers to file server',
    'Form/Admin/Service/Local.php' => 'Removed',
    'Form/Admin/Service/S3.php' => 'Added CloudFront support',
    'Model/DbTable/Files.php' => 'Fixed typo in lookupFile method; added temporary and system file support',
    'Model/DbTable/Mirrors.php' => 'Added',
    'Model/File.php' => 'Added temporary and system file support; fixed issues with deleting files when updating with newer copy; added ability to update file path',
    'Plugin/Job/Cleanup.php' => 'Added path updating and temporary file support',
    'Service/Abstract.php' => 'Removed deprecated code; changed default naming scheme; fixed issue with checking file before storing',
    'Service/Mirrored.php' => 'Added',
    'Service/RoundRobin.php' => 'Added',
    'Service/S3.php' => 'Added CloudFront support',
    'Service/Scheme/Dynamic.php' => 'Added',
    'Service/Vfs.php' => 'Added',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.0-4.1.1.sql' => 'B/c',
    'settings/my-upgrade-4.1.1-4.1.2.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-services/index.tpl' => 'Added file count to services',
  ),
  '4.1.1' => array(
    'Api/Core.php' => 'Removed deprecated code, forwards to files table',
    'Api/Storage.php' => 'Removed deprecated code, forwards to files table',
    'controllers/AdminManageController.php' => 'Added',
    'controllers/AdminServicesController.php' => 'Added',
    'externals/.htaccess' => 'Added',
    'Form/Admin/Manage/Filter.php' => 'Added',
    'Form/Admin/Service/Create.php' => 'Added',
    'Form/Admin/Service/Db.php' => 'Added',
    'Form/Admin/Service/Generic.php' => 'Added',
    'Form/Admin/Service/Local.php' => 'Added',
    'Form/Admin/Service/S3.php' => 'Added',
    'Model/DbTable/Files.php' => 'Main storage file code exists here now',
    'Model/DbTable/Services.php' => 'Added',
    'Model/DbTable/ServiceTypes.php' => 'Added',
    'Model/File.php' => 'Implemented new storage service handling for S3 adapter',
    'Plugin/Job/Cleanup.php' => 'Added',
    'Plugin/Job/Transfer.php' => 'Added',
    'Service/Abstract.php' => 'Implemented new storage service handling for S3 adapter',
    'Service/Db.php' => 'Implemented new storage service handling for S3 adapter',
    'Service/Interface.php' => 'Implemented new storage service handling for S3 adapter',
    'Service/Local.php' => 'Implemented new storage service handling for S3 adapter',
    'Service/S3.php' => 'Added',
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my-upgrade-4.1.0-4.1.1.sql' => 'Added',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/admin-manage/index.tpl' => 'Added',
    'views/scripts/admin-manage/view.tpl' => 'Added',
    'views/scripts/admin-services/create.tpl' => 'Added',
    'views/scripts/admin-services/edit.tpl' => 'Added',
    'views/scripts/admin-services/index.tpl' => 'Added',
  ),
  '4.1.0' => array(
    'settings/changelog.php' => 'Incremented version',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
    'views/scripts/upload/upload.tpl' => 'Added translation',
  ),
  '4.0.4' => array(
    'Form/Upload.php' => 'Added missing .jpeg extension to allowed extensions',
    'Plugin/Core.php' => 'Added error suppression to item delete hook',
    'Service/Abstract.php' => 'Fixed issues caused by umask',
    'settings/changelog.php' => 'Added',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.0.3' => array(
    'Api/Storage/php' => 'Fixed bug with quota handling',
    'settings/manifest.php' => 'Incremented version',
    'settings/my.sql' => 'Incremented version',
  ),
  '4.0.2' => array(
    'Api/Storage.php' => 'Typecasting storage quota values',
    'Service/Abstract.php' => 'Silencing notices in chmod',
    'settings/manifest.php' => 'Incremented version',
  ),
  '4.0.1' => array(
    'Api/Storage.php' => 'Storage quotas are now configured by member level',
    'settings/manifest.php' => 'Incremented version',
    'views/scripts/upload/upload.tpl' => 'Fixed IE JS bug',
  ),
) ?>