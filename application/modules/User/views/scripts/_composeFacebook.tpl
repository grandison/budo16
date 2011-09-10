<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _composeFacebook.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>
<?php
// prevent loading if Facebook PUBLISH is disabled
if ('publish' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) return;
// prevent loading if user is not logged into Facebook
$facebook = User_Model_DbTable_Facebook::getFBInstance(); if (!$facebook->getSession()) return;
// prevent loading if the user is not logged into the correct Facebook account
try {
  $facebook->api('/me');
  $fb_uid = Engine_Api::_()->getDbtable('facebook', 'user')->fetchRow(array('user_id = ?' => Engine_Api::_()->user()->getViewer()->getIdentity()));
  if (!$fb_uid || !$fb_uid->facebook_uid || $fb_uid->facebook_uid != $facebook->getUser())
    throw new Exception('User logged into a Facebook account other than the attached account.');
} catch (Exception $e) { return; }

$this->headScript()->appendFile($this->baseUrl() . '/application/modules/User/externals/scripts/composer_facebook.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.Facebook({
      lang : {
        'Publish this on Facebook' : '<?php echo $this->translate('Publish this on Facebook') ?>'
      }
    }));
  });
</script>
