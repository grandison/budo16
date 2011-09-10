<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Widget_LoginOrSignupController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Do not show if logged in
    if( Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
      $this->setNoRender();
      return;
    }
    
    // Display form
    $form = $this->view->form = new User_Form_Login();;
    $form->setTitle(null)->setDescription(null);
    $form->removeElement('forgot');

    // Facebook login
    if ('none' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
      $form->removeElement('facebook');
    } else {
      if ($form->getElement('facebook')) {
        $content = $form->getElement('facebook')->getContent();
        $content = str_replace('FB.Event.subscribe',
                               'FB.Event.subscribe(\'fb.log\', function(response) {
                                 window.location.reload();
                                });
                                FB.Event.subscribe',
                                $content);
        $content = str_replace('window.location.reload();',
                               sprintf('window.location.href = "%s";', Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login')),
                               $content);
        $form->getElement('facebook')->setContent($content);
      }
      
      $facebook  = User_Model_DbTable_Facebook::getFBInstance();
      if ($facebook->getSession()) {
        try {
          $me  = $facebook->api('/me');
          $uid = Engine_Api::_()->getDbtable('Facebook', 'User')->fetchRow(array('facebook_uid = ?'=>$facebook->getUser()));
          if ($uid)
              $uid = $uid->user_id;
          if ($uid) {
            // already integrated user account; sign in
            Engine_Api::_()->user()->getAuth()->getStorage()->write($uid);
          } else {
            $form->removeElement('facebook');
            //$form->setAction($this->view->url(array('controller'=>'settings','action'=>'general'), 'user_extended'));
            $form->addNotice($this->view->translate('USER_FORM_AUTH_FACEBOOK_NOACCOUNT',
                                $this->view->url(array(), 'user_signup'),
                                $this->view->url(array('controller'=>'settings','action'=>'general'), 'user_extended')));
          }
        } catch (Facebook_Exception $e) {}
      }
    }
    
  }
  
  public function getCacheKey()
  {
    return false;
  }
}