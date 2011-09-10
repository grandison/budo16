<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSettingsController.php 8150 2011-01-06 01:26:03Z char $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('forum_admin_main', array(), 'forum_admin_main_settings');

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->form = $form = new Forum_Form_Admin_Settings_Global();

    $form->bbcode->setValue($settings->getSetting('forum_bbcode', 1));
    $form->html->setValue($settings->getSetting('forum_html', 0));

    $form->topic_length->setValue($settings->getSetting('forum_topic_pagelength'));
    $form->forum_length->setValue($settings->getSetting('forum_forum_pagelength'));

    if( $this->getRequest()->isPost()&& $form->isValid($this->getRequest()->getPost()))
    {
      $values = $form->getValues();
      $settings->setSetting('forum_topic_pagelength', $values['topic_length']);
      $settings->setSetting('forum_forum_pagelength', $values['forum_length']);

      $settings->setSetting('forum_bbcode', $values['bbcode']);
      $settings->setSetting('forum_html', $values['html']);

      $form->addNotice('Your changes have been saved.');
    }
  }
}