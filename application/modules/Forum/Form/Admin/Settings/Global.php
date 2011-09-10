<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8150 2011-01-06 01:26:03Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Form_Admin_Settings_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $topic_length = new Engine_Form_Element_Text('topic_length');
    $topic_length->setDescription('How many posts will be shown per topic page? (Enter a number between 1 and 999');
    $topic_length->setLabel('Posts per topic page');
    $topic_length->setValue(25);

    $forum_length = new Engine_Form_Element_Text('forum_length');    
    $forum_length->setDescription('How topics will be shown per forum page? (Enter a number between 1 and 999');
    $forum_length->setLabel('Topics per forum page');
    $forum_length->setValue(25);
    

   // Create Elements
    $bbcode = new Engine_Form_Element_Radio('bbcode');
    $bbcode
      ->addMultiOptions(array(
        1 => 'Yes, members can use BBCode tags.',
        0 => 'No, do not let members use BBCode.'
      ));
    $bbcode->setValue(1);
    $bbcode->setLabel("Enable BBCode");

    $html = new Engine_Form_Element_Radio('html');

    $html
      ->addMultiOptions(array(
        1 => 'Yes, members can use HTML in their posts.',
        0 => 'No, strip HTML from posts.'
      ));
    $html->setValue(0);
    $html->setLabel("Enable HTML");


    // Add elements
    $this->addElements(array(
      $topic_length, 
      $forum_length,
      $bbcode,
      $html
    ));


    // Add submit button
    $submit = new Engine_Form_Element_Button('submit');
    $submit->setAttrib('type', 'submit')
      ->setLabel('Save Changes');
    $this->addElement($submit);
  }
}