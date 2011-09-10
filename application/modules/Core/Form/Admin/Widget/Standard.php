<?php

class Core_Form_Admin_Widget_Standard extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttrib('class', 'global_form_popup')
      ->setAction($_SERVER['REQUEST_URI'])
      ;

    // Element: title
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'order' => -100,
    ));


    // Element: name
    $this->addElement('Hidden', 'name', array(
      'order' => 100005,
    ));

    // Element: submit
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'order' => 100006,
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));
    
    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'onclick' => 'parent.Smoothbox.close();',
      'ignore' => true,
      'order' => 100007,
      'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
      'order' => 100008,
    ));
  }
}