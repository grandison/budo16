<?php

class Core_Form_Admin_Widget_MenuGeneric extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();

    
    // Set form attributes
    $this
      ->setTitle('Generic Menu')
      ->setDescription('Please choose a menu.');

    // Element: name
    $this->addElement('Select', 'menu', array(
      'label' => 'Menu',
    ));

    foreach( Engine_Api::_()->getDbtable('menus', 'core')->fetchAll() as $menu ) {
      $this->menu->addMultiOption($menu->name, $menu->title);
    }

    // Element: ulClass
    $this->addElement('Text', 'ulClass', array(
      'label' => 'List CSS Class',
    ));
  }
}