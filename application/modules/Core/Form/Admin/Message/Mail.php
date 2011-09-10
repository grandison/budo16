<?php

class Core_Form_Admin_Message_Mail extends Engine_Form
{

  public function init()
  {
    $description = 'Using this form, you will be able to send an email out to all of your members.  Emails are
      sent out using a queue system, so they will be sent out over time.  An email will be sent to you when
      all emails have been sent.';

    $this
      ->setTitle('Email All Members')
      ->setDescription(strtoupper(get_class($this) . '_description'));

    $settings = Engine_Api::_()->getApi('settings', 'core')->core_mail;

    if( !@$settings['queueing'] ) {
      $this->addElement('Radio', 'queueing', array(
        'label' => 'Utilize Mail Queue',
        'description' => 'Mail queueing permits the emails to be sent out over time, preventing your mail server
           from being overloaded by outgoing emails.  It is recommended you utilize mail queueing for large email
           blasts to help prevent negative performance impacts on your site.',
        'multiOptions' => array(
          1 => 'Utilize Mail Queue (recommended)',
          0 => 'Send all emails immediately (only recommended for less than 100 recipients).',
        ),
        'value' => 1,
      ));
    }


    $this->addElement('Text', 'from_address', array(
      'label' => 'From:',
      'value' => (!empty($settings['from']) ? $settings['from'] : 'noreply@' . $_SERVER['HTTP_HOST']),
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        'EmailAddress',
      )
    ));

    $this->addElement('Text', 'from_name', array(
      'label' => 'From (name):',
      'required' => true,
      'allowEmpty' => false,
      'value' => (!empty($settings['name']) ? $settings['name'] : 'Site Administrator'),
    ));

    $member_levels = array();
    $public_level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $row ) {
      if( $public_level->level_id != $row->level_id ) {
        $member_count = $row->getMembershipCount();

        if( null !== ($translate = $this->getTranslator()) ) {
          $title = $translate->translate($row->title);
        } else {
          $title = $row->title;
        }


        $member_levels[$row->level_id] = $title . ' (' . $member_count . ')';
      }
    }
    $this->addElement('Multiselect', 'target', array(
      'label' => 'Member Levels',
      'description' => 'Hold down the CTRL key to select or de-select specific Member Levels.',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => $member_levels,
      'value' => array_keys($member_levels),
    ));
    $this->target->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    $this->addElement('Text', 'subject', array(
      'label' => 'Subject:',
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('Textarea', 'body', array(
      'label' => 'Body',
      'required' => true,
      'allowEmpty' => false,
      'description' => '(HTML or Plain Text)',
    ));
    $this->body->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));


    // Advanced options
    //$this->addElement('Dummy', 'adv_toggle', array(
    //  'content' => '<a href="javascript:void(0);" onclick="$(\'adv_toggle-wrapper\').setStyle(\'display\', \'none\');$(\'fieldset-advanced\').setStyle(\'display\', \'\');">Advanced Options</a>',
    //));


    $this->addElement('Textarea', 'body_text', array(
      'label' => 'Body (text)',
    ));

    $this->addDisplayGroup(array('body_text'), 'advanced', array(
      'decorators' => array(
        'FormElements',
        array('Fieldset', array('style' => 'display:none;')),
      ),
    ));


    /*
      $this->addElement('Textarea', 'body_text', array(
      'label' => 'Body (text)',
      ));

      $this->addElement('Textarea', 'body_html', array(
      'label' => 'Body (html)',
      ));
     */

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Emails',
      'type' => 'submit',
      'ignore' => true,
    ));
  }

}