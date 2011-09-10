<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminMessageController.php 8437 2011-02-10 05:29:43Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminMessageController extends Core_Controller_Action_Admin
{
  public function mailAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Message_Mail();
    
    // let the level_ids be specified in GET string
    $level_ids = $this->_getParam('level_id', false);
    if (is_array($level_ids)) {
      $form->target->setValue($level_ids);
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $table = Engine_Api::_()->getItemTable('user');
    $select = new Zend_Db_Select($table->getAdapter());
    $select
      ->from($table->info('name'), 'email');

    $level_ids = $this->_getParam('target');
    if (is_array($level_ids) && !empty($level_ids)) {
      $select->where('level_id IN (?)', $level_ids);
    }
    
    $emails = array();
    foreach( $select->query()->fetchAll(Zend_Db::FETCH_COLUMN, 0) as $email ) {
      $emails[] = $email;
    }

    // temporarily enable queueing if requested
    $temporary_queueing = Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing;
    if (isset($values['queueing']) && $values['queueing']) {
      Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing = 1;
    }

    $mailApi = Engine_Api::_()->getApi('mail', 'core');

    $mail = $mailApi->create();
    $mail
      ->setFrom($values['from_address'], $values['from_name'])
      ->setSubject($values['subject'])
      ->setBodyHtml($values['body'])
      ;

    if( !empty($values['body_text']) ) {
      $mail->setBodyText($values['body_text']);
    } else {
      $mail->setBodyText(strip_tags($values['body']));
    }
    
    foreach( $emails as $email ) {
      $mail->addTo($email);
    }

    $mailApi->send($mail);

    $mailComplete = $mailApi->create();
    $mailComplete
      ->addTo(Engine_Api::_()->user()->getViewer()->email)
      ->setFrom($values['from_address'], $values['from_name'])
      ->setSubject('Mailing Complete: '.$values['subject'])
      ->setBodyHtml('Your email blast to your members has completed.  Please note that, while the emails have been
        sent to the recipients\' mail server, there may be a delay in them actually receiving the email due to
        spam filtering systems, incoming mail throttling features, and other systems beyond SocialEngine\'s control.')
      ;
    $mailApi->send($mailComplete);

    // emails have been queued (or sent); re-set queueing value to original if changed
    if (isset($values['queueing']) && $values['queueing']) {
      Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing = $temporary_queueing;
    }

    $this->view->form = null;
    $this->view->status = true;
  }
}