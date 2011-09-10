<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Blog_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');
/*
    $this->addElement('Radio', 'blog_public', array(
      'label' => 'Public Permissions',
      'description' => "BLOG_FORM_ADMIN_GLOBAL_BLOGPUBLIC_DESCRIPTION",
      'multiOptions' => array(
        1 => 'Yes, the public can view blogs unless they are made private.',
        0 => 'No, the public cannot view blogs.'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.public', 1),
    ));
*/
    $this->addElement('Text', 'blog_page', array(
      'label' => 'Entries Per Page',
      'description' => 'How many blog entries will be shown per page? (Enter a number between 1 and 999)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.page', 10),
    ));


    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}