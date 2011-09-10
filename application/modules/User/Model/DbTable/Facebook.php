<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Facebook.php 7566 2010-10-06 00:18:16Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Model_DbTable_Facebook extends Engine_Db_Table
{
  public static function getFBInstance()
  {
    try {
      return Zend_Registry::get('Facebook_Api');
    } catch (Exception $e) {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $facebook = new Facebook_Api(array(
        'appId'  => $settings->core_facebook_appid,
        'secret' => $settings->core_facebook_secret,
        'cookie' => true,
        'baseDomain' => $_SERVER['HTTP_HOST'],
      ));
      Zend_Registry::set('Facebook_Api', $facebook);
      return $facebook;
    }
  }
  /**
   * Generates the button used for Facebook Connect
   *
   * @param mixed $fb_params A string or array of Facebook parameters for login
   * @param string $connect_with_facebook The string to display inside the button
   * @return String Generates HTML code for facebook login button
   */
  public static function loginButton($connect_with_facebook = 'Connect with Facebook', $prevent_reload = false)
  {
    $settings  = Engine_Api::_()->getApi('settings', 'core');
    $facebook  = self::getFBInstance();

    $fb_params = array('display' => 'page', 'req_perms' => array());
    switch ($settings->core_facebook_enable) {
      case 'login':
        break;
      case 'publish':
        $fb_params['req_perms'][] = 'email';
        $fb_params['req_perms'][] = 'user_birthday';
        $fb_params['req_perms'][] = 'user_status';
        $fb_params['req_perms'][] = 'publish_stream';
        break;
      case 'none':
      default:
        return;
    }
    $fb_params['req_perms'] = implode(',', $fb_params['req_perms']);

    $fb_href    = $facebook->getLoginUrl($fb_params);
    $fb_onclick = "FB.login(null, {perms:'{$fb_params['req_perms']}'});return false;";
    return '
      <div id="fb-root"></div>
      <script type="text/javascript">
      //<![CDATA[
        (function(){
          var e = document.createElement("script");
              e.async = true;
              e.src = document.location.protocol + "//connect.facebook.net/'.Zend_Locale::findLocale().'/all.js";
          document.getElementById("fb-root").appendChild(e);
        }());
        window.fbAsyncInit = function() {
          FB.init({
            appId: "'.$settings->core_facebook_appid.'",
            status: true,
            cookie: true,
            xfbml: true
          });
          FB.Event.subscribe(\'auth.sessionChange\', function(response) {
            '.($prevent_reload ? '' : 'if (-1 != document.cookie.search(/^(.*; ?)fbs_/)) window.location.reload();').'
            }); };
          (function() {
            var e = document.createElement("script"); e.async = true; e.src = document.location.protocol + "//connect.facebook.net/'.Zend_Locale::findLocale().'/all.js";
            document.getElementById("fb-root").appendChild(e);
          }());
      //]]>
      </script>
      <a href="'.$fb_href.'" target="_blank" onclick="'.$fb_onclick.'"><img src="http://static.ak.fbcdn.net/rsrc.php/z38X1/hash/6ad3z8m6.gif" border="0" alt="'.$connect_with_facebook.'" /></a>
      ';
  }

  public static function authenticate(User_Form_Login $form)
  {
    // Facebook login
    if ('none' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
      $form->removeElement('facebook');
    } else {
      $facebook  = self::getFBInstance();
      if ($facebook->getSession()) {
        $form->removeElement('facebook');
        try {
          $me  = $facebook->api('/me');
          $uid = Engine_Api::_()->getDbtable('Facebook', 'User')->fetchRow(array('facebook_uid = ?' => $facebook->getUser()));
          if ($uid && $uid->user_id) {
            if (Engine_Api::_()->user()->getUser($uid->user_id)->getIdentity()) {
              // already integrated user account; sign in
              Engine_Api::_()->user()->getAuth()->getStorage()->write($uid->user_id);
              return true;
            } else {
              // no longer a site member
              $uid->delete();
              return false;
            }
          } else {
            $notice = Zend_Registry::get('Zend_Translate')->translate('USER_FORM_AUTH_FACEBOOK_NOACCOUNT');
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $notice = sprintf($notice, $router->assemble(array(), 'user_signup', true),
                                       $router->assemble(array('controller'=>'settings','action'=>'general'), 'user_extended', true));
            $form->addNotice($notice);
          }
        } catch (Facebook_Exception $e) {
          $log = Zend_Registry::get('Zend_Log');
          if( $log ) {
            $log->log($e->__toString(), Zend_Log::WARN);
          }
        }
      }
    }
  }
}
