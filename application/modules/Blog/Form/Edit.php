<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Blog_Form_Edit extends Blog_Form_Create
{
  public function init()
  {
    parent::init();
    $this->setTitle('Edit Blog Entry')
      ->setDescription('Edit your entry below, then click "Post Entry" to publish the entry on your blog.');
    $this->submit->setLabel('Save Changes');
  }
}